<?php

namespace App\Models;

use App\Enums\SecurityDeviceStatus;
use Database\Factories\SecurityDeviceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $security_device_lot_id
 * @property int $serial_number_scope
 * @property string $serial_number
 * @property SecurityDeviceStatus $status
 * @property int|null $security_device_lot_approval_id
 * @property Carbon|null $discarded_at
 * @property int|null $discarded_by
 * @property string|null $discard_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SecurityDeviceLot $lot
 * @property-read SecurityDeviceLotApproval|null $approval
 * @property-read User|null $discarder
 * @property-read Collection<int, Kit> $kits
 * @property-read Collection<int, BulkDispatch> $bulkDispatches
 */
#[Fillable(['security_device_lot_id', 'serial_number_scope', 'serial_number', 'status', 'discarded_at', 'discarded_by', 'discard_reason'])]
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
            'discarded_at' => 'datetime',
        ];
    }

    /**
     * Keep the devices of the given type of security device.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function ofType(Builder $query, int $securityDeviceTypeId): void
    {
        $query->whereHas('lot.announcement', fn (Builder $announcement) => $announcement->where('security_device_type_id', $securityDeviceTypeId));
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
     * Get the user who discarded the device, if it was discarded (T65).
     *
     * @return BelongsTo<User, $this>
     */
    public function discarder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'discarded_by')->withTrashed();
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
