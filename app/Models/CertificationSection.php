<?php

namespace App\Models;

use App\Enums\CertificationSectionType;
use Database\Factories\CertificationSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A section of the container that the certification inspects (H11). Its order is the order of the steps.
 *
 * @property int $id
 * @property string $name
 * @property CertificationSectionType $type
 * @property int|null $sort_order Position among the active sections; null while it is deactivated.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, SecurityDeviceType> $securityDeviceTypes
 */
#[Fillable(['name', 'type', 'sort_order'])]
class CertificationSection extends Model
{
    /** @use HasFactory<CertificationSectionFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CertificationSectionType::class,
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the security device types the inspector places in the section, with their quantity (H12), even the
     * deactivated ones: a device type can be deactivated while a section still uses it.
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
}
