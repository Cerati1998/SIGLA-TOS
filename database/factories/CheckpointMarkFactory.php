<?php

namespace Database\Factories;

use App\Models\CheckpointMark;
use App\Models\CheckpointStep;
use App\Models\SecurityDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CheckpointMark>
 */
class CheckpointMarkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'checkpoint_step_id' => CheckpointStep::factory()->verification(),
            'security_device_id' => SecurityDevice::factory(),
        ];
    }
}
