<?php

namespace App\Models;

use App\Enums\GpsDeviceCategory;
use App\Enums\GpsOpeningType;
use Database\Factories\GpsDeviceModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A model of GPS device, such as a lock for containers (H39). Its brand is a provider, and its monitoring codes are the
 * ones the monitoring platform reports for each unit, so that later every unit falls in a single model.
 *
 * @property int $id
 * @property int $brand_id
 * @property string $name
 * @property GpsDeviceCategory $category
 * @property GpsOpeningType $opening_type
 * @property string|null $monitoring_product_code
 * @property string|null $monitoring_device_type_code
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Provider $brand
 */
#[Fillable(['brand_id', 'name', 'category', 'opening_type', 'monitoring_product_code', 'monitoring_device_type_code', 'description'])]
class GpsDeviceModel extends Model
{
    /** @use HasFactory<GpsDeviceModelFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => GpsDeviceCategory::class,
            'opening_type' => GpsOpeningType::class,
        ];
    }

    /**
     * Get the brand of the model, a provider, even if it was deactivated afterwards.
     *
     * @return BelongsTo<Provider, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'brand_id')->withTrashed();
    }

    /**
     * Get the monitoring codes as shown on screen, such as «BR · 240», or null when the model has none.
     */
    public function monitoringCode(): ?string
    {
        if ($this->monitoring_product_code === null || $this->monitoring_device_type_code === null) {
            return null;
        }

        return "{$this->monitoring_product_code} · {$this->monitoring_device_type_code}";
    }
}
