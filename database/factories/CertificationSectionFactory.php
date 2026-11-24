<?php

namespace Database\Factories;

use App\Enums\CertificationSectionType;
use App\Enums\CheckpointSectionKind;
use App\Models\CertificationSection;
use App\Models\SecurityDeviceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CertificationSection>
 */
class CertificationSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->bothify('Sección ???###'),
            'type' => fake()->randomElement(CertificationSectionType::inspectionTypes()),
            'sort_order' => fake()->unique()->numberBetween(1, 1000),
        ];
    }

    /**
     * A section that carries the given quantity of the given security device type.
     */
    public function carrying(SecurityDeviceType $securityDeviceType, int $quantity): static
    {
        return $this->hasAttached($securityDeviceType, ['quantity' => $quantity], 'securityDeviceTypes');
    }

    /**
     * A checkpoint section (H33). With sections to verify it is a verification; without them it asks for photos.
     *
     * @param  list<CertificationSection>  $verifiedSections
     */
    public function checkpoint(array $verifiedSections = []): static
    {
        return $this->state(fn (): array => [
            'type' => CertificationSectionType::Checkpoint,
            'checkpoint_kind' => $verifiedSections === [] ? CheckpointSectionKind::Photos : CheckpointSectionKind::Verification,
        ])->afterCreating(fn (CertificationSection $section) => $section->verifiedSections()->sync(
            array_map(fn (CertificationSection $verified): int => $verified->id, $verifiedSections),
        ));
    }

    /**
     * A deactivated section, which is out of the order.
     */
    public function deactivated(): static
    {
        return $this->state(fn (): array => [
            'sort_order' => null,
            'deleted_at' => now(),
        ]);
    }
}
