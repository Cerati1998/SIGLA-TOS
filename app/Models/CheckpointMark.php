<?php

namespace App\Models;

use Database\Factories\CheckpointMarkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The mark of a series in a verification step of a checkpoint (H34): correct, incorrect or still unmarked. The series
 * to verify are fixed when the checkpoint is born, from the sections of the certification the step verifies. Who
 * marked it last and when is the evidence (criterion 10). The series a reinspection brought keep it, and the
 * incorrect ones it replaced keep the reinspection that superseded them, which no longer count (H35).
 *
 * @property int $id
 * @property int $checkpoint_step_id
 * @property int $security_device_id
 * @property int|null $reinspection_id
 * @property int|null $superseded_by_reinspection_id
 * @property bool|null $is_correct
 * @property int|null $marked_by
 * @property Carbon|null $marked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read CheckpointStep $step
 * @property-read SecurityDevice $device
 * @property-read User|null $marker
 * @property-read Reinspection|null $reinspection
 */
#[Fillable(['checkpoint_step_id', 'security_device_id', 'reinspection_id'])]
class CheckpointMark extends Model
{
    /** @use HasFactory<CheckpointMarkFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'marked_at' => 'datetime',
        ];
    }

    /**
     * Get the step of the mark.
     *
     * @return BelongsTo<CheckpointStep, $this>
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(CheckpointStep::class, 'checkpoint_step_id');
    }

    /**
     * Get the device whose series is marked.
     *
     * @return BelongsTo<SecurityDevice, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(SecurityDevice::class, 'security_device_id');
    }

    /**
     * Get the user who marked the series last, even if they were deactivated afterwards.
     *
     * @return BelongsTo<User, $this>
     */
    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by')->withTrashed();
    }

    /**
     * Get the reinspection that brought the series, if it was a new one (H35).
     *
     * @return BelongsTo<Reinspection, $this>
     */
    public function reinspection(): BelongsTo
    {
        return $this->belongsTo(Reinspection::class);
    }
}
