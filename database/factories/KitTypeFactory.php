<?php

namespace Database\Factories;

use App\Models\ContainerType;
use App\Models\KitType;
use App\Models\SecurityDeviceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KitType>
 */
class KitTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Kit '.fake()->unique()->numerify('####'),
            'container_type_id' => ContainerType::factory(),
        ];
    }

    /**
     * Indicate that the kit type carries the given quantity of the given security device type.
     */
    public function carrying(SecurityDeviceType $securityDeviceType, int $quantity): static
    {
        return $this->hasAttached($securityDeviceType, ['quantity' => $quantity], 'securityDeviceTypes');
    }

    /**
     * Indicate that the kit type carries the given security device type split between the internal and the external
     * assignment. Its quantity is their sum.
     */
    public function carryingSplit(SecurityDeviceType $securityDeviceType, int $internal, int $external): static
    {
        return $this->hasAttached($securityDeviceType, [
            'quantity' => $internal + $external,
            'internal_quantity' => $internal,
            'external_quantity' => $external,
        ], 'securityDeviceTypes');
    }
}
