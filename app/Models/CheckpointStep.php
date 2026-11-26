<?php

namespace App\Models;

use App\Enums\CheckpointSectionKind;
use Database\Factories\CheckpointStepFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A checkpoint section of the catalog (H33) copied into a checkpoint when it is born (H34), with what it asks for and
 * the place it had, so later changes in the catalog do not change the checkpoint. A photo step keeps its photos; a
 * verification step keeps one mark for each series the certification left in the sections it verifies.
 *
 * @property int $id
 * @property int $checkpoint_id
 * @property int $certification_section_id
 * @property CheckpointSectionKind $kind
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Checkpoint $checkpoint
 * @property-read CertificationSection $section
 * @property-read Collection<int, CheckpointPhoto> $photos
 * @property-read Collection<int, CheckpointMark> $marks
 */
#[Fillable(['checkpoint_id', 'certification_section_id', 'kind', 'position'])]
class CheckpointStep extends Model
{
    /** @use HasFactory<CheckpointStepFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => CheckpointSectionKind::class,
            'position' => 'integer',
        ];
    }

    /**
     * Get the checkpoint of the step.
     *
     * @return BelongsTo<Checkpoint, $this>
     */
    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(Checkpoint::class);
    }

    /**
     * Get the section of the catalog, even if it was deactivated afterwards.
     *
     * @return BelongsTo<CertificationSection, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(CertificationSection::class, 'certification_section_id')->withTrashed();
    }

    /**
     * Get the photos of the step, the oldest first.
     *
     * @return HasMany<CheckpointPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(CheckpointPhoto::class)->orderBy('created_at')->orderBy('id');
    }

    /**
     * Get the marks of the series of the step that count, in the order they were created: by serial number, then the
     * series each reinspection brought. The incorrect ones a reinspection replaced are left out (H35).
     *
     * @return HasMany<CheckpointMark, $this>
     */
    public function marks(): HasMany
    {
        return $this->hasMany(CheckpointMark::class)->whereNull('superseded_by_reinspection_id')->orderBy('id');
    }

    /**
     * Get the incorrect marks a reinspection replaced, kept as the trace of the first round (H35).
     *
     * @return HasMany<CheckpointMark, $this>
     */
    public function supersededMarks(): HasMany
    {
        return $this->hasMany(CheckpointMark::class)->whereNotNull('superseded_by_reinspection_id')->orderBy('id');
    }

    /**
     * Whether the step asks to mark series instead of taking photos.
     */
    public function isVerification(): bool
    {
        return $this->kind === CheckpointSectionKind::Verification;
    }

    /**
     * Whether the step is complete: a photo step with at least one photo, or a verification step with every series
     * marked (criterion 8). It reads the photos and marks already loaded.
     */
    public function isComplete(): bool
    {
        if ($this->isVerification()) {
            return $this->marks->every(fn (CheckpointMark $mark): bool => $mark->is_correct !== null);
        }

        return $this->photos->isNotEmpty();
    }
}
