<?php

namespace App\Models;

use Database\Factories\SecurityDeviceReplacementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A device of the kit, already placed in a section of a certification, replaced by an extra device of the same type,
 * that is, one that a bulk dispatch for the operation sent to that certification (H38). The removed device became
 * Descartado, with the reason and its photo; the installed one took its place in the section and in the kit. It stays
 * as the trace of the change, and the photos the section had before it no longer count. A replacement made in a
 * reinspection (H35) keeps it, with its reason.
 *
 * @property int $id
 * @property int $certification_id
 * @property int $certification_step_id
 * @property int $removed_device_id
 * @property int $installed_device_id
 * @property int $bulk_dispatch_id
 * @property int|null $reinspection_id
 * @property string $reason
 * @property int $replaced_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Certification $certification
 * @property-read CertificationStep $step
 * @property-read SecurityDevice $removedDevice
 * @property-read SecurityDevice $installedDevice
 * @property-read BulkDispatch $bulkDispatch
 * @property-read Reinspection|null $reinspection
 * @property-read User $replacer
 */
#[Fillable(['certification_id', 'certification_step_id', 'removed_device_id', 'installed_device_id', 'bulk_dispatch_id', 'reinspection_id', 'reason', 'replaced_by'])]
class SecurityDeviceReplacement extends Model
{
    /** @use HasFactory<SecurityDeviceReplacementFactory> */
    use HasFactory;

    /**
     * The longest reason, as its column.
     */
    public const int REASON_MAX = 255;

    /**
     * Get the certification whose device was replaced.
     *
     * @return BelongsTo<Certification, $this>
     */
    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    /**
     * Get the section where the device was replaced.
     *
     * @return BelongsTo<CertificationStep, $this>
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(CertificationStep::class, 'certification_step_id');
    }

    /**
     * Get the device of the kit that was taken out, now Descartado.
     *
     * @return BelongsTo<SecurityDevice, $this>
     */
    public function removedDevice(): BelongsTo
    {
        return $this->belongsTo(SecurityDevice::class, 'removed_device_id');
    }

    /**
     * Get the extra device that took its place.
     *
     * @return BelongsTo<SecurityDevice, $this>
     */
    public function installedDevice(): BelongsTo
    {
        return $this->belongsTo(SecurityDevice::class, 'installed_device_id');
    }

    /**
     * Get the bulk dispatch the extra device arrived in.
     *
     * @return BelongsTo<BulkDispatch, $this>
     */
    public function bulkDispatch(): BelongsTo
    {
        return $this->belongsTo(BulkDispatch::class);
    }

    /**
     * Get the user who replaced the device, even if they were deactivated afterwards.
     *
     * @return BelongsTo<User, $this>
     */
    public function replacer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replaced_by')->withTrashed();
    }

    /**
     * Get the reinspection it was made in, if it was (H35).
     *
     * @return BelongsTo<Reinspection, $this>
     */
    public function reinspection(): BelongsTo
    {
        return $this->belongsTo(Reinspection::class);
    }

    /**
     * Whether it was made in a reinspection (H35): then the photos it left without value are «antes de la reinspección».
     */
    public function isFromReinspection(): bool
    {
        return $this->reinspection_id !== null;
    }
}
