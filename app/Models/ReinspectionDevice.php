<?php

namespace App\Models;

use Database\Factories\ReinspectionDeviceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A device to replace in a reinspection (H35): an incorrect series of the checkpoint, with the section of the
 * certification where it was placed, which is reopened. Once replaced by an extra device (criterion 5), it keeps that
 * replacement.
 *
 * @property int $id
 * @property int $reinspection_id
 * @property int $certification_step_id
 * @property int $security_device_id
 * @property int|null $security_device_replacement_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Reinspection $reinspection
 * @property-read CertificationStep $step
 * @property-read SecurityDevice $device
 * @property-read SecurityDeviceReplacement|null $replacement
 */
#[Fillable(['reinspection_id', 'certification_step_id', 'security_device_id'])]
class ReinspectionDevice extends Model
{
    /** @use HasFactory<ReinspectionDeviceFactory> */
    use HasFactory;

    /**
     * Get the reinspection of the device.
     *
     * @return BelongsTo<Reinspection, $this>
     */
    public function reinspection(): BelongsTo
    {
        return $this->belongsTo(Reinspection::class);
    }

    /**
     * Get the reopened section where the device is placed.
     *
     * @return BelongsTo<CertificationStep, $this>
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(CertificationStep::class, 'certification_step_id');
    }

    /**
     * Get the device with the incorrect series.
     *
     * @return BelongsTo<SecurityDevice, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(SecurityDevice::class, 'security_device_id');
    }

    /**
     * Get its replacement by an extra device, once it was replaced.
     *
     * @return BelongsTo<SecurityDeviceReplacement, $this>
     */
    public function replacement(): BelongsTo
    {
        return $this->belongsTo(SecurityDeviceReplacement::class, 'security_device_replacement_id');
    }

    /**
     * Whether it was already replaced.
     */
    public function isReplaced(): bool
    {
        return $this->security_device_replacement_id !== null;
    }
}
