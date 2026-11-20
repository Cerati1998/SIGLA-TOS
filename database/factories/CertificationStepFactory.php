<?php

namespace Database\Factories;

use App\Enums\CertificationSectionType;
use App\Models\Certification;
use App\Models\CertificationSection;
use App\Models\CertificationStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CertificationStep>
 */
class CertificationStepFactory extends Factory
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
            'certification_section_id' => CertificationSection::factory(),
            'type' => CertificationSectionType::Internal,
            'position' => fake()->numberBetween(1, 50),
        ];
    }

    /**
     * A step of the external inspection.
     */
    public function external(): static
    {
        return $this->state(fn (): array => ['type' => CertificationSectionType::External]);
    }
}
