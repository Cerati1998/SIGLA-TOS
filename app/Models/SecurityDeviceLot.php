<?php

namespace App\Models;

use App\Enums\SecurityDeviceStatus;
use Database\Factories\SecurityDeviceLotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $security_device_announcement_id
 * @property int $quantity
 * @property Carbon $received_at
 * @property string $dispatch_number
 * @property string|null $notes
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SecurityDeviceAnnouncement $announcement
 * @property-read User $creator
 */
#[Fillable(['security_device_announcement_id', 'quantity', 'received_at', 'dispatch_number', 'notes', 'created_by'])]
class SecurityDeviceLot extends Model
{
    /** @use HasFactory<SecurityDeviceLotFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'received_at' => 'date',
        ];
    }

    /**
     * Get the origin of the lot.
     *
     * @return BelongsTo<SecurityDeviceAnnouncement, $this>
     */
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(SecurityDeviceAnnouncement::class, 'security_device_announcement_id');
    }

    /**
     * Get the user who entered the lot.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Get the devices of the lot.
     *
     * @return HasMany<SecurityDevice, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(SecurityDevice::class);
    }

    /**
     * Get the approvals log of the lot, the latest first (H29).
     *
     * @return HasMany<SecurityDeviceLotApproval, $this>
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(SecurityDeviceLotApproval::class)->latest('id');
    }

    /**
     * Check that the series of the lot can still be corrected: none of its devices changed status, so all of them are
     * still as they were born, Inactive or, when its type does not require approval, Available (H28, criterion 3; T62).
     */
    public function seriesCanBeCorrected(): bool
    {
        return ! $this->devices()->where('status', '!=', $this->initialDeviceStatus())->exists();
    }

    /**
     * Get the status the devices of the lot were born with, by its type (T62).
     */
    public function initialDeviceStatus(): SecurityDeviceStatus
    {
        return $this->announcement->securityDeviceType->initialDeviceStatus();
    }

    /**
     * Check whether the devices of the lot need the approval of their shipping line (H29), by its type (T62).
     */
    public function requiresApproval(): bool
    {
        return $this->announcement->securityDeviceType->requires_lot_approval;
    }

    /**
     * Count the devices of the lot in each status, with every status present.
     *
     * @return array<string, int>
     */
    public function deviceCountsByStatus(): array
    {
        $counts = $this->devices()
            ->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(SecurityDeviceStatus::cases())
            ->mapWithKeys(fn (SecurityDeviceStatus $status): array => [$status->value => (int) ($counts[$status->value] ?? 0)])
            ->all();
    }
}
