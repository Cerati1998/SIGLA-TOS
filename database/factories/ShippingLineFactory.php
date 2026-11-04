<?php

namespace Database\Factories;

use App\Models\ShippingLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingLine>
 */
class ShippingLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('???###'),
            'name' => fake()->company(),
        ];
    }
}
