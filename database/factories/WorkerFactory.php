<?php

namespace Database\Factories;

use App\Models\Identity;
use App\Models\Provider;
use App\Models\Worker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Worker>
 */
class WorkerFactory extends Factory
{
    /**
     * Define the model's default state: an own worker with a DNI.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'identity_id' => fn (): string => Identity::query()->firstOrCreate(['id' => Identity::DNI], ['description' => 'DNI'])->id,
            'document_number' => fake()->unique()->numerify('########'),
            'full_name' => fake()->name(),
            'position' => fake()->jobTitle(),
            'is_own' => true,
            'provider_id' => null,
        ];
    }

    /**
     * Indicate that the worker belongs to a provider.
     */
    public function fromProvider(?Provider $provider = null): static
    {
        return $this->state(fn (): array => [
            'is_own' => false,
            'provider_id' => $provider ?? Provider::factory(),
        ]);
    }
}
