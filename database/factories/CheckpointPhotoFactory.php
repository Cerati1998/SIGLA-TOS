<?php

namespace Database\Factories;

use App\Models\Checkpoint;
use App\Models\CheckpointPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CheckpointPhoto>
 */
class CheckpointPhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->uuid().'.jpg';

        return [
            'checkpoint_id' => Checkpoint::factory(),
            'checkpoint_step_id' => null,
            'path' => 'checkpoints/'.$name,
            'thumbnail_path' => 'checkpoints/thumbnails/'.$name,
            'uploaded_by' => User::factory(),
        ];
    }
}
