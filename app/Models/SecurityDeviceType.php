<?php

namespace App\Models;

use App\Enums\SecurityDeviceKind;
use App\Enums\SecurityDeviceStatus;
use Database\Factories\SecurityDeviceTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property SecurityDeviceKind $kind
 * @property bool $splits_internal_external
 * @property bool $uses_series
 * @property bool $requires_lot_approval
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'kind', 'splits_internal_external', 'uses_series', 'requires_lot_approval'])]
class SecurityDeviceType extends Model
{
    /** @use HasFactory<SecurityDeviceTypeFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => SecurityDeviceKind::class,
            'splits_internal_external' => 'boolean',
            'uses_series' => 'boolean',
            'requires_lot_approval' => 'boolean',
        ];
    }

    /**
     * Get the status the devices of a new lot of this type are born with: Inactive until the approval of their
     * shipping line is registered (H29), or Available when the type does not require it (T62).
     */
    public function initialDeviceStatus(): SecurityDeviceStatus
    {
        return $this->requires_lot_approval ? SecurityDeviceStatus::Inactive : SecurityDeviceStatus::Available;
    }

    /**
     * Get the scope within which the serial numbers of this type are unique (T62): 0, all of SIGLA, when the type uses
     * a series, or the type itself when it does not.
     */
    public function serialNumberScope(): int
    {
        return $this->uses_series ? 0 : $this->id;
    }

    /**
     * Check whether the type already has lots: from then on, whether it uses a series and requires the approval of its
     * lots can no longer change (T62).
     */
    public function hasLots(): bool
    {
        return $this->announcements()->exists();
    }

    /**
     * Get the origins of the lots of this type.
     *
     * @return HasMany<SecurityDeviceAnnouncement, $this>
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(SecurityDeviceAnnouncement::class);
    }
}
