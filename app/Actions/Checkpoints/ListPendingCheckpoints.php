<?php

namespace App\Actions\Checkpoints;

use App\Models\Certification;
use App\Models\CheckpointMark;
use App\Models\CheckpointPhoto;
use App\Models\CheckpointStep;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * The units whose checkpoint is pending or half done (H34, criteria 1 and 2): certifications closed and not annulled,
 * without a closed checkpoint, with how far their checkpoint went and who changed it last. The ones half done go
 * first, then the ones closed last.
 */
class ListPendingCheckpoints
{
    /**
     * Get the pending units.
     *
     * @return list<array{id: int, container: string, pouchCode: string, shippingLine: string, started: bool, done: int, total: int, lastChange: array{by: string, at: CarbonInterface}|null, closedAt: CarbonInterface}>
     */
    public function handle(): array
    {
        $certifications = $this->pending()
            ->with([
                'kit:id,pouch_code',
                'shippingLine:id,code',
                'checkpoint.steps' => fn ($steps) => $steps->with(['photos:id,checkpoint_step_id', 'marks:id,checkpoint_step_id,is_correct']),
            ])
            ->latest('closed_at')
            ->get();

        $catalogSteps = StartCheckpoint::sections()->count();
        $checkpointIds = $certifications
            ->map(fn (Certification $certification): ?int => $certification->checkpoint?->id)
            ->filter(fn (?int $id): bool => $id !== null)
            ->values()
            ->all();
        $lastChanges = $this->lastChanges($checkpointIds);

        $units = $certifications->map(function (Certification $certification) use ($catalogSteps, $lastChanges): array {
            $checkpoint = $certification->checkpoint;
            /** @var CarbonInterface $closedAt */
            $closedAt = $certification->closed_at;

            return [
                'id' => $certification->id,
                'container' => $certification->container,
                'pouchCode' => $certification->kit->pouch_code,
                'shippingLine' => $certification->shippingLine->code,
                'started' => $checkpoint !== null,
                'done' => $checkpoint?->steps->filter(fn (CheckpointStep $step): bool => $step->isComplete())->count() ?? 0,
                'total' => $checkpoint?->steps->count() ?? $catalogSteps,
                'lastChange' => $checkpoint === null ? null : ($lastChanges[$checkpoint->id] ?? null),
                'closedAt' => $closedAt,
            ];
        });

        return array_values($units->sortBy(fn (array $unit): int => $unit['started'] ? 0 : 1)->all());
    }

    /**
     * Get who changed each checkpoint last and when: the latest of its photos and of its marks.
     *
     * @param  array<int, int>  $checkpointIds
     * @return array<int, array{by: string, at: CarbonInterface}>
     */
    private function lastChanges(array $checkpointIds): array
    {
        if ($checkpointIds === []) {
            return [];
        }

        $changes = [];

        CheckpointPhoto::query()->with('uploader:id,name')->whereIn('checkpoint_id', $checkpointIds)->get()
            ->each(function (CheckpointPhoto $photo) use (&$changes): void {
                $this->keepLatest($changes, $photo->checkpoint_id, $photo->uploader, $photo->created_at);
            });

        CheckpointMark::query()->with(['marker:id,name', 'step:id,checkpoint_id'])->whereNotNull('marked_at')
            ->whereHas('step', fn (Builder $steps) => $steps->whereIn('checkpoint_id', $checkpointIds))->get()
            ->each(function (CheckpointMark $mark) use (&$changes): void {
                $this->keepLatest($changes, $mark->step->checkpoint_id, $mark->marker, $mark->marked_at);
            });

        return $changes;
    }

    /**
     * Keep a change of a checkpoint if it is later than the one already kept.
     *
     * @param  array<int, array{by: string, at: CarbonInterface}>  $changes
     */
    private function keepLatest(array &$changes, int $checkpointId, ?User $user, ?CarbonInterface $at): void
    {
        if ($user === null || $at === null) {
            return;
        }

        if (! isset($changes[$checkpointId]) || $at->greaterThan($changes[$checkpointId]['at'])) {
            $changes[$checkpointId] = ['by' => $user->name, 'at' => $at];
        }
    }

    /**
     * Get the pending unit of a certification, the one opened from the list.
     */
    public function certification(int $certificationId): Certification
    {
        return $this->pending()->findOrFail($certificationId);
    }

    /**
     * Get the certifications closed and not annulled whose checkpoint is not closed.
     *
     * @return Builder<Certification>
     */
    private function pending(): Builder
    {
        return Certification::query()
            ->whereNotNull('closed_at')
            ->whereNull('annulled_at')
            ->whereDoesntHave('checkpoint', fn (Builder $checkpoint) => $checkpoint->whereNotNull('closed_at'));
    }
}
