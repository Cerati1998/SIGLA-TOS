<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\ShippingLine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SecurityDeviceAnnouncement>
 */
class SecurityDeviceAnnouncementFactory extends Factory
{
    /**
     * Define the model's default state: the reception of a dispatch, with no lots yet.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipping_line_id' => ShippingLine::factory(),
            'provider_id' => Provider::factory(),
            'received_at' => fake()->date(),
            'dispatch_number' => fake()->numerify('T001-######'),
            'created_by' => User::factory(),
        ];
    }
}
