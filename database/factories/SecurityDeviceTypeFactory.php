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
        ];
    }
}
