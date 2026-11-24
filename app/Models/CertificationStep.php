<?php

namespace App\Models;

use App\Enums\CertificationSectionType;
use Database\Factories\CertificationStepFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A section of the catalog (H11) copied into a certification when the kit is assigned, with the devices of the kit
 * the system placed in it (H18). The copy keeps the type and the position the section had, so later changes in the
 * catalog do not change the certification. Its inspection is the internal or the external one, by its type.
 *
 * @property int $id
 * @property int $certification_id
 * @property int $certification_section_id
 * @property CertificationSectionType $type
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Certification $certification
 * @property-read CertificationSection $section
 * @property-read Collection<int, SecurityDevice> $devices
 * @property-read Collection<int, CertificationPhoto> $photos
 * @property-read Collection<int, CertificationPhoto> $currentPhotos
 * @property-read Collection<int, SecurityDeviceReplacement> $replacements
 */
#[Fillable(['certification_id', 'certification_section_id', 'type', 'position'])]
class CertificationStep extends Model
{
    /** @use HasFactory<CertificationStepFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CertificationSectionType::class,
            'position' => 'integer',
        ];
    }

    /**
     * Get the certification of the step.
     *
     * @return BelongsTo<Certification, $this>
     */
    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    /**
     * Get the section of the catalog, even if it was deactivated afterwards.
     *
     * @return BelongsTo<CertificationSection, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(CertificationSection::class, 'certification_section_id')->withTrashed();
    }

    /**
     * Get the devices of the kit placed in the section, by serial number.
     *
     * @return BelongsToMany<SecurityDevice, $this>
     */
    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(SecurityDevice::class)->withTimestamps()->orderBy('security_devices.serial_number');
    }

    /**
     * Get the photos of the section, the oldest first.
     *
     * @return HasMany<CertificationPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(CertificationPhoto::class)->orderBy('created_at')->orderBy('id');
    }

    /**
     * Get the photos that count, the oldest first: the ones taken after the last replacement of a device of the section
     * (H38, criterion 6). The section is done when it has at least one of them.
     *
     * @return HasMany<CertificationPhoto, $this>
     */
    public function currentPhotos(): HasMany
    {
        return $this->photos()->whereNull('superseded_by_replacement_id');
    }

    /**
     * Get the replacements of devices of the section, the oldest first (H38).
     *
     * @return HasMany<SecurityDeviceReplacement, $this>
     */
    public function replacements(): HasMany
    {
        return $this->hasMany(SecurityDeviceReplacement::class)->orderBy('created_at')->orderBy('id');
    }
}
