<?php

namespace App\Models;

use App\Enums\CheckpointStatus;
use App\Services\StoredPhoto;
use Carbon\CarbonInterface;
use Database\Factories\CheckpointFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;

/**
 * The checkpoint of a certification (H34): one per certification, born the first time someone opens the unit, with the
 * checkpoint sections of the catalog copied as its steps. It keeps who started it (H36), the optional photo of the
 * container, the observations and who closed it and when. A closed checkpoint is not edited. With incorrect series it starts a reinspection
 * (H35) and waits for it, not editable, until it is closed; then it verifies the new series.
 *
 * @property int $id
 * @property int $certification_id
 * @property int|null $started_by
 * @property string|null $container_photo_path
 * @property string|null $container_thumbnail_path
 * @property int|null $container_photo_by
 * @property Carbon|null $container_photo_at
 * @property string|null $observations
 * @property Carbon|null $closed_at
 * @property int|null $closed_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Certification $certification
 * @property-read User|null $starter
 * @property-read User|null $containerPhotographer
 * @property-read User|null $closer
 * @property-read Collection<int, CheckpointStep> $steps
 * @property-read Collection<int, CheckpointPhoto> $photos
 * @property-read Collection<int, CheckpointPhoto> $extraPhotos
 * @property-read Collection<int, CheckpointMark> $allMarks
 * @property-read Collection<int, Reinspection> $reinspections
 * @property-read Reinspection|null $openReinspection
 * @property-read Reinspection|null $lastReinspection
 */
#[Fillable(['certification_id', 'started_by', 'observations'])]
class Checkpoint extends Model
{
    /** @use HasFactory<CheckpointFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'container_photo_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Get the certification of the checkpoint.
     *
     * @return BelongsTo<Certification, $this>
     */
    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    /**
     * Get the user who started the checkpoint, opening the unit for the first time, even if they were deactivated
     * afterwards (H36). Null in the checkpoints born before it was kept.
     *
     * @return BelongsTo<User, $this>
     */
    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by')->withTrashed();
    }

    /**
     * Get the user who took the photo of the container, even if they were deactivated afterwards.
     *
     * @return BelongsTo<User, $this>
     */
    public function containerPhotographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'container_photo_by')->withTrashed();
    }

    /**
     * Get the user who closed the checkpoint, even if they were deactivated afterwards.
     *
     * @return BelongsTo<User, $this>
     */
    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by')->withTrashed();
    }

    /**
     * Get the steps of the checkpoint, in the order the checkpoint sections had when it was born.
     *
     * @return HasMany<CheckpointStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(CheckpointStep::class)->orderBy('position')->orderBy('id');
    }

    /**
     * Get every photo of the checkpoint: the ones of its steps and the extra ones.
     *
     * @return HasMany<CheckpointPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(CheckpointPhoto::class)->orderBy('created_at')->orderBy('id');
    }

    /**
     * Get the extra photos of the last step, which belong to no step (criterion 7).
     *
     * @return HasMany<CheckpointPhoto, $this>
     */
    public function extraPhotos(): HasMany
    {
        return $this->photos()->whereNull('checkpoint_step_id');
    }

    /**
     * Get every mark of its steps, also the incorrect ones a reinspection replaced, to know who worked on it (H36).
     *
     * @return HasManyThrough<CheckpointMark, CheckpointStep, $this>
     */
    public function allMarks(): HasManyThrough
    {
        return $this->hasManyThrough(CheckpointMark::class, CheckpointStep::class);
    }

    /**
     * Get the reinspections it started, the first one first (H35).
     *
     * @return HasMany<Reinspection, $this>
     */
    public function reinspections(): HasMany
    {
        return $this->hasMany(Reinspection::class)->orderBy('id');
    }

    /**
     * Get the reinspection still open, while the certified inspector replaces the series (H35).
     *
     * @return HasOne<Reinspection, $this>
     */
    public function openReinspection(): HasOne
    {
        return $this->hasOne(Reinspection::class)->whereNull('closed_at');
    }

    /**
     * Get the reinspection started last, open or closed (H35).
     *
     * @return HasOne<Reinspection, $this>
     */
    public function lastReinspection(): HasOne
    {
        return $this->hasOne(Reinspection::class)->latestOfMany();
    }

    /**
     * Whether it waits for a reinspection: then it is not edited and it is not in the list of «Registrar checkpoint».
     */
    public function isInReinspection(): bool
    {
        return (bool) ($this->getAttribute('open_reinspection_exists') ?? $this->openReinspection()->exists());
    }

    /**
     * Get where the checkpoint is (H36, criterion 1).
     */
    public function status(): CheckpointStatus
    {
        return match (true) {
            $this->isClosed() && $this->incorrectMarks()->isNotEmpty() => CheckpointStatus::ClosedWithObservation,
            $this->isClosed() => CheckpointStatus::Closed,
            $this->isInReinspection() => CheckpointStatus::InReinspection,
            default => CheckpointStatus::InProgress,
        };
    }

    /**
     * Keep the checkpoints in a status.
     *
     * @param  Builder<Checkpoint>  $query
     */
    #[Scope]
    protected function whereStatus(Builder $query, CheckpointStatus $status): void
    {
        match ($status) {
            CheckpointStatus::Closed => $query->whereNotNull('checkpoints.closed_at')->whereDoesntHave('allMarks', self::incorrect(...)),
            CheckpointStatus::ClosedWithObservation => $query->whereNotNull('checkpoints.closed_at')->whereHas('allMarks', self::incorrect(...)),
            CheckpointStatus::InReinspection => $query->whereNull('checkpoints.closed_at')->has('openReinspection'),
            CheckpointStatus::InProgress => $query->whereNull('checkpoints.closed_at')->doesntHave('openReinspection'),
        };
    }

    /**
     * Keep the marks that count and are incorrect: a closed checkpoint only keeps them in the steps that do not ask for a
     * reinspection (T93).
     *
     * @param  Builder<CheckpointMark>  $marks
     */
    private static function incorrect(Builder $marks): void
    {
        $marks->where('checkpoint_marks.is_correct', false)->whereNull('checkpoint_marks.superseded_by_reinspection_id');
    }

    /**
     * Get the incorrect marks that count, from the steps loaded with their marks.
     *
     * @return SupportCollection<int, CheckpointMark>
     */
    public function incorrectMarks(): SupportCollection
    {
        return $this->steps->flatMap(fn (CheckpointStep $step) => $step->incorrectMarks())->values()->toBase();
    }

    /**
     * Get the incorrect marks that ask for a reinspection, from the steps loaded with their marks (T93).
     *
     * @return SupportCollection<int, CheckpointMark>
     */
    public function reinspectionMarks(): SupportCollection
    {
        return $this->steps->flatMap(fn (CheckpointStep $step) => $step->reinspectionMarks())->values()->toBase();
    }

    /**
     * Whether an incorrect series is in a step that does not ask for a reinspection: then the observations are required
     * to close it or to start a reinspection (T93). It reads the steps loaded with their marks.
     */
    public function needsObservation(): bool
    {
        return $this->steps->contains(fn (CheckpointStep $step): bool => $step->skips_reinspection && $step->incorrectMarks()->isNotEmpty());
    }

    /**
     * Keep the checkpoints a user worked on: started, closed, took one of its photos or marked one of its series (H36,
     * decision of the user of 2026-11-26).
     *
     * @param  Builder<Checkpoint>  $query
     */
    #[Scope]
    protected function workedOnBy(Builder $query, int $userId): void
    {
        $query->where(fn (Builder $checkpoints) => $checkpoints
            ->where('checkpoints.started_by', $userId)
            ->orWhere('checkpoints.closed_by', $userId)
            ->orWhereHas('photos', fn (Builder $photos) => $photos->where('uploaded_by', $userId))
            ->orWhereHas('allMarks', fn (Builder $marks) => $marks->where('marked_by', $userId)));
    }

    /**
     * Get how far it went, from the steps loaded with their photos and marks: the complete steps and the series that
     * count, with how many are marked, correct and incorrect.
     *
     * @return array{steps: int, stepsDone: int, series: int, marked: int, correct: int, incorrect: int}
     */
    public function progress(): array
    {
        $marks = $this->steps->flatMap(fn (CheckpointStep $step) => $step->marks);

        return [
            'steps' => $this->steps->count(),
            'stepsDone' => $this->steps->filter(fn (CheckpointStep $step): bool => $step->isComplete())->count(),
            'series' => $marks->count(),
            'marked' => $marks->filter(fn (CheckpointMark $mark): bool => $mark->is_correct !== null)->count(),
            'correct' => $marks->filter(fn (CheckpointMark $mark): bool => $mark->is_correct === true)->count(),
            'incorrect' => $marks->filter(fn (CheckpointMark $mark): bool => $mark->is_correct === false)->count(),
        ];
    }

    /**
     * Get who works on it, from what was loaded: who closed it or, while it is not closed, who took its last photo or
     * marked its last series, or else who started it (H36, decision of the user of 2026-11-26).
     */
    public function inspectorName(): ?string
    {
        if ($this->isClosed()) {
            return $this->closer?->name;
        }

        $photos = $this->photos->map(fn (CheckpointPhoto $photo): array => ['at' => $photo->created_at, 'by' => $photo->uploader->name]);
        $marks = $this->steps->flatMap(fn (CheckpointStep $step) => $step->marks)
            ->filter(fn (CheckpointMark $mark): bool => $mark->marked_at !== null && $mark->marker !== null)
            ->map(fn (CheckpointMark $mark): array => ['at' => $mark->marked_at, 'by' => $mark->marker?->name]);
        $last = $photos->concat($marks)
            ->sortBy(fn (array $change): int => (int) $change['at']?->getTimestamp())
            ->last();

        return $last['by'] ?? $this->starter?->name;
    }

    /**
     * Get when it changed last, from what was loaded: the latest of its start, its photos, its marks, its
     * reinspections and its closing.
     */
    public function lastChangeAt(): CarbonInterface
    {
        /** @var CarbonInterface $createdAt */
        $createdAt = $this->created_at;

        /** @var CarbonInterface $last */
        $last = collect([$createdAt, $this->closed_at])
            ->concat($this->photos->pluck('created_at'))
            ->concat($this->steps->flatMap(fn (CheckpointStep $step) => $step->marks)->pluck('marked_at'))
            ->concat($this->reinspections->pluck('created_at'))
            ->concat($this->reinspections->pluck('closed_at'))
            ->filter()
            ->sortBy(fn (CarbonInterface $at): int => $at->getTimestamp())
            ->last();

        return $last;
    }

    /**
     * Whether the checkpoint is closed: then it is not edited (criterion 11).
     */
    public function isClosed(): bool
    {
        return $this->closed_at !== null;
    }

    /**
     * Get the files of the photo of the container, if it has one.
     */
    public function storedContainerPhoto(): ?StoredPhoto
    {
        if ($this->container_photo_path === null || $this->container_thumbnail_path === null) {
            return null;
        }

        return new StoredPhoto($this->container_photo_path, $this->container_thumbnail_path);
    }
}
