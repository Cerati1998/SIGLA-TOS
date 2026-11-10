<?php

namespace App\Models;

use Database\Factories\SecurityDeviceAnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * The origin of a lot: where it comes from and how its serial numbers are built.
 *
 * @property int $id
 * @property int $shipping_line_id
 * @property int $provider_id
 * @property int $security_device_type_id
 * @property string $series_prefix
 * @property int $min_digits
 * @property int $series_start
 * @property int $series_end
 * @property string|null $series_suffix
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ShippingLine $shippingLine
 * @property-read Provider $provider
 * @property-read SecurityDeviceType $securityDeviceType
 * @property-read SecurityDeviceLot|null $lot
 */
#[Fillable(['shipping_line_id', 'provider_id', 'security_device_type_id', 'series_prefix', 'min_digits', 'series_start', 'series_end', 'series_suffix'])]
class SecurityDeviceAnnouncement extends Model
{
    /** @use HasFactory<SecurityDeviceAnnouncementFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_digits' => 'integer',
            'series_start' => 'integer',
            'series_end' => 'integer',
        ];
    }

    /**
     * Get the serial number of a correlative: the prefix, the correlative left-padded with zeros up to the minimum of digits,
     * and the suffix when there is one. A correlative longer than the minimum stays whole.
     */
    public function serialNumber(int $correlative): string
    {
        return $this->series_prefix.$this->correlativeWithSuffix($correlative);
    }

    /**
     * Get the range of the lot without the prefix, as in "0000200 – 0000399".
     */
    public function rangeLabel(): string
    {
        return $this->correlativeWithSuffix($this->series_start).' – '.$this->correlativeWithSuffix($this->series_end);
    }

    /**
     * Get the name of the lot: its prefix and its range, as in "GSW 0000200 – 0000399".
     */
    public function lotName(): string
    {
        return $this->series_prefix.' '.$this->rangeLabel();
    }

    /**
     * Get a correlative left-padded with zeros and followed by the suffix, without the prefix.
     */
    private function correlativeWithSuffix(int $correlative): string
    {
        return str_pad((string) $correlative, $this->min_digits, '0', STR_PAD_LEFT).$this->series_suffix;
    }

    /**
     * Get the serial numbers of the whole range, from the first correlative to the last one.
     *
     * @return list<string>
     */
    public function serialNumbers(): array
    {
        return array_map($this->serialNumber(...), range($this->series_start, $this->series_end));
    }

    /**
     * Get the shipping line the devices are for.
     *
     * @return BelongsTo<ShippingLine, $this>
     */
    public function shippingLine(): BelongsTo
    {
        return $this->belongsTo(ShippingLine::class)->withTrashed();
    }

    /**
     * Get the provider that sends the devices.
     *
     * @return BelongsTo<Provider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class)->withTrashed();
    }

    /**
     * Get the type of the devices.
     *
     * @return BelongsTo<SecurityDeviceType, $this>
     */
    public function securityDeviceType(): BelongsTo
    {
        return $this->belongsTo(SecurityDeviceType::class)->withTrashed();
    }

    /**
     * Get the lot received from this announcement.
     *
     * @return HasOne<SecurityDeviceLot, $this>
     */
    public function lot(): HasOne
    {
        return $this->hasOne(SecurityDeviceLot::class);
    }
}
