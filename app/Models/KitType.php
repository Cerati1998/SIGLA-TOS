<?php

namespace App\Models;

use Database\Factories\KitTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A kit type: for which container type it is and how many of each security device type it carries.
 *
 * @property int $id
 * @property string $name
 * @property int $container_type_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read ContainerType $containerType
 * @property-read Collection<int, SecurityDeviceType> $securityDeviceTypes
 */
#[Fillable(['name', 'container_type_id'])]
class KitType extends Model
{
    /** @use HasFactory<KitTypeFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the container type of the kit type, even if it was deactivated.
     *
     * @return BelongsTo<ContainerType, $this>
     */
    public function containerType(): BelongsTo
    {
        return $this->belongsTo(ContainerType::class)->withTrashed();
    }

    /**
     * Get the security device types the kit type carries, with their quantity, even the deactivated ones: a device
     * type can be deactivated while a kit type still uses it.
     *
     * @return BelongsToMany<SecurityDeviceType, $this>
     */
    public function securityDeviceTypes(): BelongsToMany
    {
        return $this->belongsToMany(SecurityDeviceType::class)
            ->withPivot('quantity')
            ->withTrashed()
            ->orderBy('security_device_types.name');
    }

    /**
     * Get how many security devices the kit type carries in total.
     */
    public function totalDevices(): int
    {
        return (int) $this->securityDeviceTypes->sum('pivot.quantity');
    }
}
