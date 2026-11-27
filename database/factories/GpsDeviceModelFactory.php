<?php

namespace Database\Factories;

use App\Enums\GpsDeviceCategory;
use App\Enums\GpsOpeningType;
use App\Models\GpsDeviceModel;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GpsDeviceModel>
 */
class GpsDeviceModelFactory extends Factory
{
    /**
     * Define the model's default state: a container lock with remote opening and no monitoring codes.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'brand_id' => Provider::factory(),
            'name' => fake()->unique()->bothify('Modelo ??-###'),
            'category' => GpsDeviceCategory::ContainerLock,
            'opening_type' => GpsOpeningType::Remote,
            'monitoring_product_code' => null,
            'monitoring_device_type_code' => null,
            'description' => null,
        ];
    }

    /**
     * Indicate the codes the monitoring platform reports for the units of the model.
     */
    public function withMonitoringCodes(string $productCode = 'BR', ?string $deviceTypeCode = null): static
    {
        return $this->state(fn (): array => [
            'monitoring_product_code' => $productCode,
            'monitoring_device_type_code' => $deviceTypeCode ?? (string) fake()->unique()->numberBetween(100, 999),
        ]);
    }
}
