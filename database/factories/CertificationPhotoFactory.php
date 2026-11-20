<?php

namespace Database\Factories;

use App\Models\CertificationPhoto;
use App\Models\CertificationStep;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CertificationPhoto>
 */
class CertificationPhotoFactory extends Factory
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
            'certification_step_id' => CertificationStep::factory(),
            'path' => 'certifications/'.$name,
            'thumbnail_path' => 'certifications/thumbnails/'.$name,
            'uploaded_by' => User::factory(),
        ];
    }
}
