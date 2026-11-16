<?php

namespace Database\Factories;

use App\Enums\SecurityDeviceKind;
use App\Models\SecurityDeviceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SecurityDeviceType>
 */
class SecurityDeviceTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->bothify('Tipo ???###'),
            'kind' => fake()->randomElement(SecurityDeviceKind::cases()),
            'splits_internal_external' => false,
        ];
    }

    /**
     * A type whose quantity in a kit type is split between the internal and the external assignment, as the
     * inspection tape.
     */
    public function splitsInternalExternal(): static
    {
        return $this->state(fn (array $attributes): array => ['splits_internal_external' => true]);
    }
}
