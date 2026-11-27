<?php

namespace Database\Factories;

use App\Enums\GpsDeviceOwnership;
use App\Enums\GpsDeviceStatus;
use App\Models\Branch;
use App\Models\GpsDevice;
use App\Models\GpsDeviceModel;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GpsDevice>
 */
class GpsDeviceFactory extends Factory
{
    /**
     * Define the model's default state: an own device, available, without photo nor id in the monitoring platform.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('HBR###??'),
            'monitoring_asset_id' => null,
            'gps_device_model_id' => GpsDeviceModel::factory(),
            'ownership' => GpsDeviceOwnership::Own,
            'rental_provider_id' => null,
            'branch_id' => Branch::factory(),
            'status' => GpsDeviceStatus::Available,
            'notes' => null,
            'photo_path' => null,
            'thumbnail_path' => null,
        ];
    }

    /**
     * Indicate that the device is rented from a provider.
     */
    public function rented(?Provider $provider = null): static
    {
        return $this->state(fn (): array => [
            'ownership' => GpsDeviceOwnership::Rented,
            'rental_provider_id' => $provider ?? Provider::factory(),
        ]);
    }
}
