<?php

namespace Database\Factories;

use App\Enums\SecurityDeviceStatus;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceLot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SecurityDevice>
 */
class SecurityDeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'security_device_lot_id' => SecurityDeviceLot::factory(),
            'serial_number' => fake()->unique()->bothify('???#######'),
            'status' => SecurityDeviceStatus::Available,
        ];
    }
}
