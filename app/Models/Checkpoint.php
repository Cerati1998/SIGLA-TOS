<?php

namespace App\Models;

use App\Services\StoredPhoto;
use Database\Factories\CheckpointFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * The checkpoint of a certification (H34): one per certification, born the first time someone opens the unit, with the
 * checkpoint sections of the catalog copied as its steps. It keeps the optional photo of the container, the observations
 * and who closed it and when. A closed checkpoint is not edited. With incorrect series it starts a reinspection
 * (H35) and waits for it, not editable, until it is closed; then it verifies the new series.
 *
 * @property int $id
 * @property int $certification_id
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
 * @property-read User|null $containerPhotographer
 * @property-read User|null $closer
 * @property-read Collection<int, CheckpointStep> $steps
 * @property-read Collection<int, CheckpointPhoto> $photos
 * @property-read Collection<int, CheckpointPhoto> $extraPhotos
 * @property-read Collection<int, Reinspection> $reinspections
 * @property-read Reinspection|null $openReinspection
 * @property-read Reinspection|null $lastReinspection
 */
#[Fillable(['certification_id', 'observations'])]
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
        return $this->openReinspection()->exists();
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
