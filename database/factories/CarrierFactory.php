<?php

namespace Database\Factories;

use App\Models\Carrier;
use App\Models\Identity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Carrier>
 */
class CarrierFactory extends Factory
{
    /**
     * Define the model's default state: a carrier with its RUC.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'identity_id' => fn (): string => Identity::query()->firstOrCreate(['id' => Identity::RUC], ['description' => 'RUC'])->id,
            'document_number' => fake()->unique()->numerify('20#########'),
            'business_name' => fake()->company(),
            'address' => fake()->address(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('9########'),
        ];
    }
}
