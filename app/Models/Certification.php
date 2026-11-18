<?php

namespace App\Models;

use Database\Factories\CertificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The certification of a unit, started by an inspector with a gate out request of SIGLA 1.0 and a dispatched kit
 * (H17). It keeps only the reference, the booking and the container of the request, to look it up again in
 * SIGLA 1.0, and the links to the catalogs; the client, the product and the driver's DNI and license are never
 * copied.
 *
 * @property int $id
 * @property string $gate_out_request_reference
 * @property string $booking
 * @property string $container
 * @property int $shipping_line_id
 * @property int|null $carrier_id
 * @property int|null $driver_id
 * @property int|null $tractor_id
 * @property int|null $semi_trailer_id
 * @property int $kit_id
 * @property string|null $inspection_sheet_code
 * @property int $yard_zone_id
 * @property int $certified_by
 * @property Carbon $started_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ShippingLine $shippingLine
 * @property-read Carrier|null $carrier
 * @property-read Driver|null $driver
 * @property-read Vehicle|null $tractor
 * @property-read Vehicle|null $semiTrailer
 * @property-read Kit $kit
 * @property-read YardZone $yardZone
 * @property-read User $certifier
 */
#[Fillable([
    'gate_out_request_reference', 'booking', 'container', 'shipping_line_id', 'carrier_id', 'driver_id', 'tractor_id',
    'semi_trailer_id', 'kit_id', 'inspection_sheet_code', 'yard_zone_id', 'certified_by', 'started_at',
])]
class Certification extends Model
{
    /** @use HasFactory<CertificationFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
        ];
    }

    /**
     * Get the shipping line of the request, even if it was deactivated afterwards.
     *
     * @return BelongsTo<ShippingLine, $this>
     */
    public function shippingLine(): BelongsTo
    {
        return $this->belongsTo(ShippingLine::class)->withTrashed();
    }

    /**
     * Get the carrier of the request, even if it was deactivated afterwards.
     *
     * @return BelongsTo<Carrier, $this>
     */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class)->withTrashed();
    }

    /**
     * Get the driver of the request, even if it was deactivated afterwards.
     *
     * @return BelongsTo<Driver, $this>
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class)->withTrashed();
    }

    /**
     * Get the tractor of the request, even if it was deactivated afterwards.
     *
     * @return BelongsTo<Vehicle, $this>
     */
    public function tractor(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'tractor_id')->withTrashed();
    }

    /**
     * Get the semi-trailer of the request, even if it was deactivated afterwards.
     *
     * @return BelongsTo<Vehicle, $this>
     */
    public function semiTrailer(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'semi_trailer_id')->withTrashed();
    }

    /**
     * Get the kit used in the certification.
     *
     * @return BelongsTo<Kit, $this>
     */
    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class);
    }

    /**
     * Get the yard zone where the unit is certified, even if it was deactivated afterwards.
     *
     * @return BelongsTo<YardZone, $this>
     */
    public function yardZone(): BelongsTo
    {
        return $this->belongsTo(YardZone::class)->withTrashed();
    }

    /**
     * Get the inspector who started the certification.
     *
     * @return BelongsTo<User, $this>
     */
    public function certifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certified_by')->withTrashed();
    }
}
