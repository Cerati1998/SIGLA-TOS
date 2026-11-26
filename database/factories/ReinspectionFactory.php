<?php

namespace Database\Factories;

use App\Models\Checkpoint;
use App\Models\Reinspection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reinspection>
 */
class ReinspectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'checkpoint_id' => Checkpoint::factory(),
            'certification_id' => fn (array $attributes): int => (int) Checkpoint::query()->whereKey($attributes['checkpoint_id'])->value('certification_id'),
            'reason' => 'La cinta del evaporador está rota.',
            'started_by' => User::factory(),
        ];
    }
}
