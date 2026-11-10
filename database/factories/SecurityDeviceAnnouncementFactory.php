<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SecurityDeviceAnnouncement>
 */
class SecurityDeviceAnnouncementFactory extends Factory
{
    /**
     * Define the model's default state: an announcement of the serial numbers GSW0000200 to GSW0000399.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipping_line_id' => ShippingLine::factory(),
            'provider_id' => Provider::factory(),
            'security_device_type_id' => SecurityDeviceType::factory(),
            'series_prefix' => 'GSW',
            'min_digits' => 7,
            'series_start' => 200,
            'series_end' => 399,
            'series_suffix' => null,
        ];
    }
}
