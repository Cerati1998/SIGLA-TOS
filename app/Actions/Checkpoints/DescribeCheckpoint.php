<?php

namespace App\Actions\Checkpoints;

use App\Models\CertificationStep;
use App\Models\Checkpoint;
use App\Models\CheckpointMark;
use App\Models\CheckpointPhoto;
use App\Models\CheckpointStep;
use App\Models\Reinspection;
use App\Models\ReinspectionDevice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

/**
 * What the detail of a checkpoint shows, only to read (H36, criterion 3): each step with its photos or with its series
 * and their marks, the observations and the extra photos, and each reinspection with its reason, who started and
 * closed it, the devices replaced and the second verification of the new series. The series a reinspection replaced
 * stay beside the one that came in.
 *
 * @phpstan-type MarkRow array{mark: CheckpointMark, superseded: bool, round: int|null}
 * @phpstan-type Description array{steps: Collection<int, CheckpointStep>, markRows: array<int, list<MarkRow>>, extraPhotos: Collection<int, CheckpointPhoto>, reinspections: Collection<int, Reinspection>, rounds: array<int, int>, secondMarks: array<int, list<CheckpointMark>>, deviceSections: array<int, string>, photos: int, series: int}
 */
class DescribeCheckpoint
{
    /**
     * Describe the checkpoint, loading everything it shows at once.
     *
     * @return Description
     */
    public function handle(Checkpoint $checkpoint): array
    {
        $checkpoint->loadMissing([
            'certification.shippingLine', 'certification.carrier', 'certification.driver', 'certification.tractor',
            'certification.semiTrailer', 'certification.kit', 'starter', 'closer', 'openReinspection',
        ]);
        $steps = $checkpoint->steps()
            ->with(['section.verifiedSections', 'photos.uploader', 'marks.device', 'marks.marker', 'supersededMarks.device', 'supersededMarks.marker'])
            ->get();
        $reinspections = $checkpoint->reinspections()
            ->with(['starter', 'closer', 'devices.device', 'devices.step.section', 'devices.replacement.installedDevice', 'devices.replacement.bulkDispatch'])
            ->get();
        $rounds = $reinspections->values()->mapWithKeys(fn (Reinspection $reinspection, int $index): array => [$reinspection->id => $index + 1])->all();
        $replaced = $this->replacedDevices($reinspections);
        $extraPhotos = $checkpoint->extraPhotos()->with('uploader')->get();

        return [
            'steps' => $steps,
            'markRows' => $steps->mapWithKeys(fn (CheckpointStep $step): array => [$step->id => $this->markRows($step, $replaced, $rounds)])->all(),
            'extraPhotos' => $extraPhotos,
            'reinspections' => $reinspections,
            'rounds' => $rounds,
            'secondMarks' => $this->secondMarks($steps),
            'deviceSections' => $this->deviceSections($checkpoint, $reinspections),
            'photos' => $steps->sum(fn (CheckpointStep $step): int => $step->photos->count()) + $extraPhotos->count(),
            'series' => $steps->sum(fn (CheckpointStep $step): int => $step->marks->count()),
        ];
    }

    /**
     * Get, for each device a reinspection put in, the device it replaced.
     *
     * @param  Collection<int, Reinspection>  $reinspections
     * @return array<int, int>
     */
    private function replacedDevices(Collection $reinspections): array
    {
        return $reinspections->flatMap(fn (Reinspection $reinspection) => $reinspection->devices)
            ->filter(fn (ReinspectionDevice $device): bool => $device->replacement !== null)
            ->mapWithKeys(fn (ReinspectionDevice $device): array => [(int) $device->replacement?->installed_device_id => $device->security_device_id])
            ->all();
    }

    /**
     * Get the series of a verification step in the order they are shown: each one of the first round, and right after
     * an incorrect one a reinspection replaced, the series that came in its place.
     *
     * @param  array<int, int>  $replaced
     * @param  array<int, int>  $rounds
     * @return list<MarkRow>
     */
    private function markRows(CheckpointStep $step, array $replaced, array $rounds): array
    {
        $superseded = $step->supersededMarks->map(fn (CheckpointMark $mark): array => ['mark' => $mark, 'superseded' => true]);
        $all = $step->marks->map(fn (CheckpointMark $mark): array => ['mark' => $mark, 'superseded' => false])
            ->concat($superseded)
            ->sortBy(fn (array $row): int => $row['mark']->id)
            ->map(fn (array $row): array => [...$row, 'round' => $row['mark']->reinspection_id === null ? null : ($rounds[$row['mark']->reinspection_id] ?? null)]);

        /** @var list<MarkRow> $rows */
        $rows = $all->filter(fn (array $row): bool => $row['mark']->reinspection_id === null)->values()->all();

        foreach ($all->filter(fn (array $row): bool => $row['mark']->reinspection_id !== null) as $row) {
            $removedId = $replaced[$row['mark']->security_device_id] ?? null;
            $position = $this->lastPositionOf($rows, $removedId);

            if ($position === null) {
                $rows[] = $row;
            } else {
                array_splice($rows, $position + 1, 0, [$row]);
            }
        }

        return $rows;
    }

    /**
     * Get where the mark of a device is in the rows, or null.
     *
     * @param  list<MarkRow>  $rows
     */
    private function lastPositionOf(array $rows, ?int $deviceId): ?int
    {
        if ($deviceId === null) {
            return null;
        }

        $position = null;

        foreach ($rows as $index => $row) {
            if ($row['mark']->security_device_id === $deviceId) {
                $position = $index;
            }
        }

        return $position;
    }

    /**
     * Get, for each reinspection, the marks of the series it brought: the second verification.
     *
     * @param  Collection<int, CheckpointStep>  $steps
     * @return array<int, list<CheckpointMark>>
     */
    private function secondMarks(Collection $steps): array
    {
        /** @var BaseCollection<int, CheckpointMark> $marks */
        $marks = $steps->flatMap(fn (CheckpointStep $step) => $step->marks->concat($step->supersededMarks))
            ->filter(fn (CheckpointMark $mark): bool => $mark->reinspection_id !== null);

        return $marks->groupBy('reinspection_id')
            ->map(fn (BaseCollection $group): array => array_values($group->sortBy('id')->all()))
            ->all();
    }

    /**
     * Get, for each device of the certification, the section it is in, also for the ones a reinspection took out.
     *
     * @param  Collection<int, Reinspection>  $reinspections
     * @return array<int, string>
     */
    private function deviceSections(Checkpoint $checkpoint, Collection $reinspections): array
    {
        $sections = [];

        $reinspections->flatMap(fn (Reinspection $reinspection) => $reinspection->devices)
            ->each(function (ReinspectionDevice $device) use (&$sections): void {
                $sections[$device->security_device_id] = $device->step->section->name;
            });

        $checkpoint->certification->steps()->with(['section', 'devices:id'])->get()
            ->each(function (CertificationStep $step) use (&$sections): void {
                foreach ($step->devices as $device) {
                    $sections[$device->id] = $step->section->name;
                }
            });

        return $sections;
    }
}
