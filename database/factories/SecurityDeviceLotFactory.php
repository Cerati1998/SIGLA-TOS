<?php

namespace Database\Factories;

use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SecurityDeviceLot>
 */
class SecurityDeviceLotFactory extends Factory
{
    /**
     * Define the model's default state: a lot of the serial numbers GSW0000200 to GSW0000399, without its devices.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'security_device_announcement_id' => SecurityDeviceAnnouncement::factory(),
            'security_device_type_id' => SecurityDeviceType::factory(),
            'series_prefix' => 'GSW',
            'min_digits' => 7,
            'series_start' => 200,
            'series_end' => 399,
            'series_suffix' => null,
            'quantity' => 200,
            'notes' => null,
        ];
    }
}
