<?php

namespace Database\Factories;

use App\Models\VehicleBody;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleBody>
 */
class VehicleBodyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'CARROCERÍA '.fake()->unique()->numerify('####'),
        ];
    }
}
