<?php

namespace App\Models;

use App\Enums\GpsConnectivity;
use App\Enums\GpsDeviceOwnership;
use App\Enums\GpsDeviceStatus;
use Database\Factories\GpsDeviceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A physical GPS device, such as a lock with the code HBR140 (H40): its model, whether it is own or rented, its branch
 * and its id in the monitoring platform, to check its connectivity (H42). Deactivating it gives it its discharge.
 *
 * @property int $id
 * @property string $code
 * @property int|null $monitoring_asset_id
 * @property int $gps_device_model_id
 * @property GpsDeviceOwnership $ownership
 * @property int|null $rental_provider_id
 * @property int $branch_id
 * @property GpsDeviceStatus $status
 * @property string|null $notes
 * @property string|null $photo_path
 * @property string|null $thumbnail_path
 * @property Carbon|null $last_reported_at
 * @property string|null $last_latitude
 * @property string|null $last_longitude
 * @property string|null $last_address
 * @property string|null $service_status
 * @property string|null $operational_status
 * @property string|null $supply_voltage
 * @property Carbon|null $last_checked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read GpsDeviceModel $gpsDeviceModel
 * @property-read Provider|null $rentalProvider
 * @property-read Branch $branch
 */
#[Fillable(['code', 'monitoring_asset_id', 'gps_device_model_id', 'ownership', 'rental_provider_id', 'branch_id', 'status', 'notes', 'photo_path', 'thumbnail_path', 'last_reported_at', 'last_latitude', 'last_longitude', 'last_address', 'service_status', 'operational_status', 'supply_voltage', 'last_checked_at'])]
class GpsDevice extends Model
{
    /** @use HasFactory<GpsDeviceFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monitoring_asset_id' => 'integer',
            'ownership' => GpsDeviceOwnership::class,
            'status' => GpsDeviceStatus::class,
            'last_reported_at' => 'datetime',
            'last_latitude' => 'decimal:7',
            'last_longitude' => 'decimal:7',
            'supply_voltage' => 'decimal:2',
            'last_checked_at' => 'datetime',
        ];
    }

    /**
     * Get the model of the device, even if it was deactivated afterwards.
     *
     * @return BelongsTo<GpsDeviceModel, $this>
     */
    public function gpsDeviceModel(): BelongsTo
    {
        return $this->belongsTo(GpsDeviceModel::class)->withTrashed();
    }

    /**
     * Get the provider that rents the device, even if it was deactivated afterwards. Null for an own device.
     *
     * @return BelongsTo<Provider, $this>
     */
    public function rentalProvider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'rental_provider_id')->withTrashed();
    }

    /**
     * Get the branch of the device, even if it was deactivated afterwards.
     *
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    /**
     * Get whether the device reports to 24HM, judged by its last report saved (H42).
     */
    public function connectivity(): GpsConnectivity
    {
        if ($this->last_reported_at === null) {
            return GpsConnectivity::Unchecked;
        }

        return $this->last_reported_at->greaterThan(now()->subHours(GpsConnectivity::RECENT_HOURS))
            ? GpsConnectivity::Connected
            : GpsConnectivity::NoSignal;
    }
}
