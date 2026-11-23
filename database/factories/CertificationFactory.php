<?php

namespace Database\Factories;

use App\Enums\KitStatus;
use App\Models\Certification;
use App\Models\Kit;
use App\Models\ShippingLine;
use App\Models\User;
use App\Models\YardZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certification>
 */
class CertificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gate_out_request_reference' => (string) fake()->unique()->numberBetween(10000, 99999),
            'booking' => fake()->bothify('BKG#########'),
            'container' => fake()->bothify('????#######'),
            'shipping_line_id' => ShippingLine::factory(),
            'kit_id' => Kit::factory()->state(['status' => KitStatus::Dispatched]),
            'yard_zone_id' => YardZone::factory(),
            'certified_by' => User::factory(),
            'started_at' => now(),
        ];
    }
}
