<?php

namespace App\Models;

use App\Enums\VehicleCategory;
use App\Enums\VehicleKind;
use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A tractor or a semi-trailer, identified by its plate. It does not belong to any carrier.
 *
 * @property int $id
 * @property string $plate
 * @property string $brand
 * @property VehicleKind $kind
 * @property int $vehicle_body_id
 * @property VehicleCategory $category
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read VehicleBody $body
 */
#[Fillable(['plate', 'brand', 'kind', 'vehicle_body_id', 'category'])]
class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the plate as it is stored: in capitals, with only letters and numbers, such as ABC123.
     */
    public static function normalizePlate(string $plate): string
    {
        return mb_strtoupper((string) preg_replace('/[\s-]+/', '', $plate));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => VehicleKind::class,
            'category' => VehicleCategory::class,
        ];
    }

    /**
     * Get the body of the vehicle, even if the body was deactivated.
     *
     * @return BelongsTo<VehicleBody, $this>
     */
    public function body(): BelongsTo
    {
        return $this->belongsTo(VehicleBody::class, 'vehicle_body_id')->withTrashed();
    }
}
