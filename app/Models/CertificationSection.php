<?php

namespace App\Models;

use App\Enums\CertificationSectionType;
use App\Enums\CheckpointSectionKind;
use Database\Factories\CertificationSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A section of the container that the certification inspects (H11), or a step of the checkpoint (H33). Its order is
 * the order of the steps: one order for all of them, each inspection and the checkpoint take only their own.
 *
 * @property int $id
 * @property string $name
 * @property CertificationSectionType $type
 * @property CheckpointSectionKind|null $checkpoint_kind What a checkpoint section asks for; null for the other types.
 * @property int|null $sort_order Position among the active sections; null while it is deactivated.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, SecurityDeviceType> $securityDeviceTypes
 * @property-read Collection<int, CertificationSection> $verifiedSections
 */
#[Fillable(['name', 'type', 'checkpoint_kind', 'sort_order'])]
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
            'checkpoint_kind' => CheckpointSectionKind::class,
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

    /**
     * Get the name of a security device type the section carries, with its side when the type is split between the
     * internal and the external assignment (T84): the side is the type of the section.
     */
    public function deviceTypeName(SecurityDeviceType $deviceType): string
    {
        if (! $deviceType->splits_internal_external || ! $this->type->isInspection()) {
            return $deviceType->name;
        }

        return "{$deviceType->name} · {$this->type->label()}";
    }

    /**
     * Get the internal or external sections whose serial numbers a checkpoint section of verification shows (H33),
     * even the deactivated ones: the link stays until the admin removes it.
     *
     * @return BelongsToMany<CertificationSection, $this>
     */
    public function verifiedSections(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'certification_section_verifications', 'checkpoint_section_id', 'verified_section_id')
            ->withTrashed()
            ->orderByRaw('certification_sections.sort_order is null')
            ->orderBy('certification_sections.sort_order')
            ->orderBy('certification_sections.name');
    }
}
