<?php

namespace Database\Factories;

use App\Models\Certification;
use App\Models\Checkpoint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Checkpoint>
 */
class CheckpointFactory extends Factory
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
        ];
    }

    /**
     * A checkpoint already closed.
     */
    public function closed(): static
    {
        return $this->state(fn (): array => [
            'closed_at' => now(),
            'closed_by' => User::factory(),
        ]);
    }
}
