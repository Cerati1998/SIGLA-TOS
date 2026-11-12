<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * The link between a carrier and one of its drivers. A driver can work for several carriers, and is deactivated in
 * each one on its own.
 *
 * @property int $id
 * @property int $carrier_id
 * @property int $driver_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Carrier $carrier
 * @property-read Driver $driver
 */
#[Fillable(['carrier_id', 'driver_id'])]
class CarrierDriver extends Model
{
    use SoftDeletes;

    /**
     * The table of the model.
     *
     * @var string
     */
    protected $table = 'carrier_driver';

    /**
     * Get the carrier of the link.
     *
     * @return BelongsTo<Carrier, $this>
     */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    /**
     * Get the driver of the link.
     *
     * @return BelongsTo<Driver, $this>
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
