<?php

namespace App\Models;

use App\Enums\SecurityDeviceStatus;
use Database\Factories\SecurityDeviceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $security_device_lot_id
 * @property string $serial_number
 * @property SecurityDeviceStatus $status
 * @property int|null $security_device_lot_approval_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SecurityDeviceLot $lot
 * @property-read SecurityDeviceLotApproval|null $approval
 * @property-read Collection<int, Kit> $kits
 * @property-read Collection<int, BulkDispatch> $bulkDispatches
 */
#[Fillable(['security_device_lot_id', 'serial_number', 'status'])]
class SecurityDevice extends Model
{
    /** @use HasFactory<SecurityDeviceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SecurityDeviceStatus::class,
        ];
    }

    /**
     * Get the lot the device came in.
     *
     * @return BelongsTo<SecurityDeviceLot, $this>
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(SecurityDeviceLot::class, 'security_device_lot_id');
    }

    /**
     * Get the approval that made the device available, if it was approved already (H29).
     *
     * @return BelongsTo<SecurityDeviceLotApproval, $this>
     */
    public function approval(): BelongsTo
    {
        return $this->belongsTo(SecurityDeviceLotApproval::class, 'security_device_lot_approval_id');
    }

    /**
     * Get the kit the device is in, as a list of at most one: a device is only in one kit (H14, criterion 6).
     *
     * @return BelongsToMany<Kit, $this>
     */
    public function kits(): BelongsToMany
    {
        return $this->belongsToMany(Kit::class)->withPivot('id', 'assignment')->withTimestamps();
    }

    /**
     * Get the bulk dispatch the device left in, as a list of at most one: a device is only in one (H30, criterion 10).
     *
     * @return BelongsToMany<BulkDispatch, $this>
     */
    public function bulkDispatches(): BelongsToMany
    {
        return $this->belongsToMany(BulkDispatch::class, 'bulk_dispatch_items')->withTimestamps();
    }
}
