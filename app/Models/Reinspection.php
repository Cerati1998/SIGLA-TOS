<?php

namespace App\Models;

use Database\Factories\ReinspectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A reinspection that a checkpoint started with a reason because some series were incorrect (H35). In the
 * certification only the sections of those series are reopened: the certified inspector replaces each series by an
 * extra device and takes new photos, then closes it, and the checkpoint verifies the new series again. A checkpoint
 * can have several, one after the other.
 *
 * @property int $id
 * @property int $checkpoint_id
 * @property int $certification_id
 * @property string $reason
 * @property int $started_by
 * @property Carbon|null $closed_at
 * @property int|null $closed_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Checkpoint $checkpoint
 * @property-read Certification $certification
 * @property-read User $starter
 * @property-read User|null $closer
 * @property-read Collection<int, ReinspectionDevice> $devices
 */
#[Fillable(['checkpoint_id', 'certification_id', 'reason', 'started_by'])]
class Reinspection extends Model
{
    /** @use HasFactory<ReinspectionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Get the checkpoint that started it.
     *
     * @return BelongsTo<Checkpoint, $this>
     */
    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(Checkpoint::class);
    }

    /**
     * Get the certification whose sections it reopened.
     *
     * @return BelongsTo<Certification, $this>
     */
    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    /**
     * Get the user who started it, even if they were deactivated afterwards.
     *
     * @return BelongsTo<User, $this>
     */
    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by')->withTrashed();
    }

    /**
     * Get the user who closed it, even if they were deactivated afterwards.
     *
     * @return BelongsTo<User, $this>
     */
    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by')->withTrashed();
    }

    /**
     * Get the devices to replace: one for each incorrect series, with the section where it is.
     *
     * @return HasMany<ReinspectionDevice, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(ReinspectionDevice::class)->orderBy('id');
    }

    /**
     * Whether it was closed: then the checkpoint verifies the new series.
     */
    public function isClosed(): bool
    {
        return $this->closed_at !== null;
    }

    /**
     * Get the ids of the reopened sections of the certification, in the order of the devices.
     *
     * @return list<int>
     */
    public function reopenedStepIds(): array
    {
        return array_values(array_unique($this->devices->pluck('certification_step_id')->map(fn ($id): int => (int) $id)->all()));
    }

    /**
     * Get how many reopened sections it has and how many are done: each with its devices replaced and at least one
     * photo taken after them (criterion 7). It reads the database.
     *
     * @return array{total: int, done: int}
     */
    public function progress(): array
    {
        $stepIds = $this->reopenedStepIds();
        $pending = $this->devices->whereNull('security_device_replacement_id')->pluck('certification_step_id')->map(fn ($id): int => (int) $id)->all();
        $withPhoto = CertificationStep::query()->whereKey($stepIds)->has('currentPhotos')->pluck('id')->map(fn ($id): int => (int) $id)->all();

        $done = array_filter($stepIds, fn (int $stepId): bool => ! in_array($stepId, $pending, true) && in_array($stepId, $withPhoto, true));

        return ['total' => count($stepIds), 'done' => count($done)];
    }

    /**
     * Whether every reopened section is done, the only way to close it.
     */
    public function isComplete(): bool
    {
        $progress = $this->progress();

        return $progress['total'] > 0 && $progress['done'] === $progress['total'];
    }
}
