<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ruc' => fake()->unique()->numerify('20#########'),
            'business_name' => fake()->company(),
            'address' => fake()->address(),
            'ubigeo' => fake()->numerify('######'),
        ];
    }
}
