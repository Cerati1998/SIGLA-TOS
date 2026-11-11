<?php

namespace App\Models;

use Database\Factories\VehicleBodyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A vehicle body, such as REMOLCADOR or PLATAFORMA. The same list serves tractors and semi-trailers.
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $vehicles_count
 */
#[Fillable(['name'])]
class VehicleBody extends Model
{
    /** @use HasFactory<VehicleBodyFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the name as it is stored: in capitals and with single spaces, as SIGLA 1.0 sends it.
     */
    public static function normalizeName(string $name): string
    {
        return mb_strtoupper(trim((string) preg_replace('/\s+/', ' ', $name)));
    }

    /**
     * Get the vehicles with this body, the deactivated ones too.
     *
     * @return HasMany<Vehicle, $this>
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class)->withTrashed();
    }
}
