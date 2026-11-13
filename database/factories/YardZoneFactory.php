<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\YardZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<YardZone>
 */
class YardZoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => 'Zona '.fake()->unique()->bothify('??-###'),
            'capacity' => fake()->numberBetween(10, 300),
        ];
    }
}
