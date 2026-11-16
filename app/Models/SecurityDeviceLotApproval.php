<?php

namespace App\Models;

use Database\Factories\SecurityDeviceLotApprovalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One record of the approvals log of a lot: the stretch of correlatives that bodega approved, after the shipping line
 * confirmed it, and how many inactive devices became available.
 *
 * @property int $id
 * @property int $security_device_lot_id
 * @property int $correlative_start
 * @property int $correlative_end
 * @property int $approved_count
 * @property int $approved_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SecurityDeviceLot $lot
 * @property-read User $approver
 */
#[Fillable(['security_device_lot_id', 'correlative_start', 'correlative_end', 'approved_count', 'approved_by'])]
class SecurityDeviceLotApproval extends Model
{
    /** @use HasFactory<SecurityDeviceLotApprovalFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'correlative_start' => 'integer',
            'correlative_end' => 'integer',
            'approved_count' => 'integer',
        ];
    }

    /**
     * Get the lot the approval belongs to.
     *
     * @return BelongsTo<SecurityDeviceLot, $this>
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(SecurityDeviceLot::class, 'security_device_lot_id');
    }

    /**
     * Get the user who registered the approval, even if it was deactivated.
     *
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by')->withTrashed();
    }

    /**
     * Get the devices that became available with this approval.
     *
     * @return HasMany<SecurityDevice, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(SecurityDevice::class);
    }
}
