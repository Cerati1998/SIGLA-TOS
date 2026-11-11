<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Identity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state: a driver with a DNI and a Peruvian license, a letter before the DNI.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'identity_id' => fn (): string => Identity::query()->firstOrCreate(['id' => Identity::DNI], ['description' => 'DNI'])->id,
            'document_number' => fake()->unique()->numerify('########'),
            'full_name' => mb_strtoupper(fake()->lastName().' '.fake()->lastName().' '.fake()->firstName()),
            'license' => fn (array $attributes): string => 'B'.$attributes['document_number'],
        ];
    }
}
