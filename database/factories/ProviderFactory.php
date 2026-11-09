<?php

namespace Database\Factories;

use App\Models\Identity;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    /**
     * Define the model's default state: a provider with a RUC.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'identity_id' => fn (): string => $this->identity(Identity::RUC, 'RUC'),
            'document_number' => fake()->unique()->numerify('20#########'),
            'business_name' => fake()->company(),
            'address' => fake()->address(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('9########'),
        ];
    }

    /**
     * Indicate that the provider is a person identified by a DNI.
     */
    public function withDni(): static
    {
        return $this->state(fn (): array => [
            'identity_id' => $this->identity(Identity::DNI, 'DNI'),
            'document_number' => fake()->unique()->numerify('########'),
            'business_name' => fake()->name(),
        ]);
    }

    /**
     * Get the given document type, creating it when the database has no catalog loaded.
     */
    private function identity(string $id, string $description): string
    {
        return Identity::query()->firstOrCreate(['id' => $id], ['description' => $description])->id;
    }
}
