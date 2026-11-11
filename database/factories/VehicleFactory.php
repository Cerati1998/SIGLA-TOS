<?php

namespace Database\Factories;

use App\Enums\VehicleCategory;
use App\Enums\VehicleKind;
use App\Models\Vehicle;
use App\Models\VehicleBody;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state: a tractor.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plate' => strtoupper(fake()->unique()->bothify('???###')),
            'brand' => mb_strtoupper(fake()->company()),
            'kind' => VehicleKind::Tractor,
            'vehicle_body_id' => VehicleBody::factory(),
            'category' => VehicleCategory::N3,
            'color' => mb_strtoupper(fake()->safeColorName()),
        ];
    }
}
