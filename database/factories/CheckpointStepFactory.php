<?php

namespace Database\Factories;

use App\Enums\CheckpointSectionKind;
use App\Models\CertificationSection;
use App\Models\Checkpoint;
use App\Models\CheckpointStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CheckpointStep>
 */
class CheckpointStepFactory extends Factory
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
            'certification_section_id' => CertificationSection::factory()->checkpoint(),
            'kind' => CheckpointSectionKind::Photos,
            'position' => fake()->numberBetween(1, 50),
        ];
    }

    /**
     * A step that verifies series.
     */
    public function verification(): static
    {
        return $this->state(fn (): array => ['kind' => CheckpointSectionKind::Verification]);
    }
}
