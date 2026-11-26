<?php

namespace App\Actions\Certifications;

use App\Enums\CertificationSectionType;
use App\Models\Certification;
use App\Models\CertificationPhoto;
use App\Models\Reinspection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * The assigned units whose internal or external inspection still has a section without a photo, for the card of that
 * inspection (H18, criterion 2), with how far it went and how the other inspection goes. A unit in reinspection is
 * here while a reopened section of that inspection is still pending, marked, with only its reopened sections (H35,
 * criterion 4).
 */
class ListPendingInspections
{
    /**
     * Get the pending units of an inspection, the ones assigned last first.
     *
     * @return list<array{id: int, container: string, pouchCode: string, shippingLine: string, done: int, total: int, otherDone: int, otherTotal: int, lastPhoto: CertificationPhoto|null, startedAt: Carbon, reinspection: bool}>
     */
    public function handle(CertificationSectionType $type): array
    {
        $other = $type === CertificationSectionType::Internal ? CertificationSectionType::External : CertificationSectionType::Internal;

        /** @var Collection<int, Certification> $certifications */
        $certifications = $this->withProgress(Certification::query()->whereNull('annulled_at')->doesntHave('openReinspection'), $type, $other)
            ->with(['kit:id,pouch_code', 'shippingLine:id,code'])
            ->latest('started_at')
            ->get()
            ->filter(fn (Certification $certification): bool => $this->count($certification, 'done') < $this->count($certification, 'total'));

        $reinspections = $this->reinspections($type);
        $lastPhotos = $this->lastPhotos([...$certifications->modelKeys(), ...array_keys($reinspections)], $type);

        $units = $certifications
            ->map(fn (Certification $certification): array => [
                'id' => $certification->id,
                'container' => $certification->container,
                'pouchCode' => $certification->kit->pouch_code,
                'shippingLine' => $certification->shippingLine->code,
                'done' => $this->count($certification, 'done'),
                'total' => $this->count($certification, 'total'),
                'otherDone' => $this->count($certification, 'other_done'),
                'otherTotal' => $this->count($certification, 'other_total'),
                'lastPhoto' => $lastPhotos[$certification->id] ?? null,
                'startedAt' => $certification->started_at,
                'reinspection' => false,
            ])
            ->all();

        $reinspectionUnits = array_map(fn (array $unit): array => [...$unit, 'lastPhoto' => $lastPhotos[$unit['id']] ?? null], array_values($reinspections));

        return [...$reinspectionUnits, ...array_values($units)];
    }

    /**
     * Get the units in reinspection with a reopened section of this inspection still pending: without its new device
     * or without a new photo. Their progress counts only the reopened sections.
     *
     * @return array<int, array{id: int, container: string, pouchCode: string, shippingLine: string, done: int, total: int, otherDone: int, otherTotal: int, lastPhoto: null, startedAt: Carbon, reinspection: bool}>
     */
    private function reinspections(CertificationSectionType $type): array
    {
        $units = [];

        Reinspection::query()
            ->whereNull('closed_at')
            ->whereHas('certification', fn (Builder $certifications) => $certifications->whereNull('annulled_at'))
            ->with(['devices.step' => fn ($steps) => $steps->withExists('currentPhotos'), 'certification.kit:id,pouch_code', 'certification.shippingLine:id,code'])
            ->latest()
            ->get()
            ->each(function (Reinspection $reinspection) use ($type, &$units): void {
                $mine = $this->reopenedProgress($reinspection, $type);

                if ($mine['total'] === 0 || $mine['done'] === $mine['total']) {
                    return;
                }

                $other = $this->reopenedProgress($reinspection, $type === CertificationSectionType::Internal ? CertificationSectionType::External : CertificationSectionType::Internal);
                $certification = $reinspection->certification;

                $units[$certification->id] = [
                    'id' => $certification->id,
                    'container' => $certification->container,
                    'pouchCode' => $certification->kit->pouch_code,
                    'shippingLine' => $certification->shippingLine->code,
                    'done' => $mine['done'],
                    'total' => $mine['total'],
                    'otherDone' => $other['done'],
                    'otherTotal' => $other['total'],
                    'lastPhoto' => null,
                    'startedAt' => $certification->started_at,
                    'reinspection' => true,
                ];
            });

        return $units;
    }

    /**
     * Count the reopened sections of an inspection and the done ones: with their devices replaced and a new photo.
     *
     * @return array{total: int, done: int}
     */
    private function reopenedProgress(Reinspection $reinspection, CertificationSectionType $type): array
    {
        $steps = $reinspection->devices->groupBy('certification_step_id')
            ->filter(fn ($devices) => $devices->first()?->step->type === $type);

        $done = $steps->filter(fn ($devices) => $devices->every(fn ($device): bool => $device->isReplaced())
            && (bool) $devices->first()?->step->getAttribute('current_photos_exists'));

        return ['total' => $steps->count(), 'done' => $done->count()];
    }

    /**
     * Get how many units have each inspection pending, for the cards of the panel.
     *
     * @return array{internal: int, external: int}
     */
    public function counts(): array
    {
        return [
            'internal' => count($this->handle(CertificationSectionType::Internal)),
            'external' => count($this->handle(CertificationSectionType::External)),
        ];
    }

    /**
     * Count the sections of each inspection and the ones with a photo.
     *
     * @param  Builder<Certification>  $query
     * @return Builder<Certification>
     */
    private function withProgress(Builder $query, CertificationSectionType $type, CertificationSectionType $other): Builder
    {
        return $query->withCount([
            'steps as total' => fn (Builder $steps) => $steps->where('type', $type),
            'steps as done' => fn (Builder $steps) => $steps->where('type', $type)->has('currentPhotos'),
            'steps as other_total' => fn (Builder $steps) => $steps->where('type', $other),
            'steps as other_done' => fn (Builder $steps) => $steps->where('type', $other)->has('currentPhotos'),
        ]);
    }

    /**
     * Get the last photo of the inspection of each certification, with who uploaded it.
     *
     * @param  array<int, int|string>  $certificationIds
     * @return array<int, CertificationPhoto>
     */
    private function lastPhotos(array $certificationIds, CertificationSectionType $type): array
    {
        return CertificationPhoto::query()
            ->with(['uploader:id,name', 'step:id,certification_id'])
            ->whereHas('step', fn (Builder $steps) => $steps->whereIn('certification_id', $certificationIds)->where('type', $type))
            ->latest()
            ->get()
            ->unique(fn (CertificationPhoto $photo): int => $photo->step->certification_id)
            ->mapWithKeys(fn (CertificationPhoto $photo): array => [$photo->step->certification_id => $photo])
            ->all();
    }

    private function count(Certification $certification, string $attribute): int
    {
        return (int) $certification->getAttribute($attribute);
    }
}
