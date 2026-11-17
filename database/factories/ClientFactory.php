<?php

namespace Database\Factories;

use App\Enums\PaymentCondition;
use App\Models\Client;
use App\Models\Identity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state: a client with a RUC that pays in cash.
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
            'payment_condition' => PaymentCondition::Cash,
        ];
    }

    /**
     * Indicate that the client is a person identified by a DNI.
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
     * Indicate that the client pays on credit, in 30 days.
     */
    public function onCredit(): static
    {
        return $this->state(fn (): array => ['payment_condition' => PaymentCondition::Credit, 'credit_days' => 30]);
    }

    /**
     * Get the given document type, creating it when the database has no catalog loaded.
     */
    private function identity(string $id, string $description): string
    {
        return Identity::query()->firstOrCreate(['id' => $id], ['description' => $description])->id;
    }
}
