<?php

namespace Database\Factories;

use App\Models\ContainerType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContainerType>
 */
class ContainerTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('##??'),
            'iso_code' => fake()->unique()->bothify('##?#'),
            'description' => "40'-ST Standard",
            'is_reefer' => false,
            'length' => '12.19',
            'width' => '2.44',
            'height' => '2.59',
        ];
    }

    /**
     * Indicate that the container type is a reefer.
     */
    public function reefer(): static
    {
        return $this->state(fn (array $attributes): array => [
            'description' => "40'-RFHC Reefer High Cube",
            'is_reefer' => true,
        ]);
    }
}
