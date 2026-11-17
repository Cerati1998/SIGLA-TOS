<?php

namespace Database\Factories;

use App\Enums\KitStatus;
use App\Models\Kit;
use App\Models\KitType;
use App\Models\ShippingLine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kit>
 */
class KitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pouch_code' => fake()->unique()->numerify('KIT-######'),
            'kit_type_id' => KitType::factory(),
            'shipping_line_id' => ShippingLine::factory(),
            'status' => KitStatus::Building,
            'created_by' => User::factory(),
        ];
    }
}
