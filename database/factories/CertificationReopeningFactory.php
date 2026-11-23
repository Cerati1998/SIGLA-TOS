<?php

namespace Database\Factories;

use App\Models\Certification;
use App\Models\CertificationReopening;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CertificationReopening>
 */
class CertificationReopeningFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'certification_id' => Certification::factory(),
            'reason' => fake()->sentence(),
            'reopened_by' => User::factory(),
            'closed_at' => now()->subHour(),
            'closed_by' => User::factory(),
        ];
    }
}
