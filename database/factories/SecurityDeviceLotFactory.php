<?php

namespace Database\Factories;

use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SecurityDeviceLot>
 */
class SecurityDeviceLotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'security_device_announcement_id' => SecurityDeviceAnnouncement::factory(),
            'quantity' => 200,
            'received_at' => fake()->date(),
            'dispatch_number' => fake()->numerify('T001-######'),
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}
