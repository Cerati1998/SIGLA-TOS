<?php

namespace Database\Factories;

use App\Enums\DispatchMode;
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
            'dispatch_mode' => DispatchMode::Random,
        ];
    }

    /**
     * A line whose devices leave in the order of their correlatives (T82).
     */
    public function linear(): static
    {
        return $this->state(['dispatch_mode' => DispatchMode::Linear]);
    }
}
