<?php

namespace App\Models;

use App\Enums\SecurityDeviceStatus;
use Database\Factories\SecurityDeviceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $security_device_lot_id
 * @property string $serial_number
 * @property SecurityDeviceStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SecurityDeviceLot $lot
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
}
