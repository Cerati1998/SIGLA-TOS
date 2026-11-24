<?php

namespace Database\Factories;

use App\Models\BulkDispatch;
use App\Models\Certification;
use App\Models\CertificationStep;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceReplacement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SecurityDeviceReplacement>
 */
class SecurityDeviceReplacementFactory extends Factory
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
            'certification_step_id' => CertificationStep::factory(),
            'removed_device_id' => SecurityDevice::factory(),
            'installed_device_id' => SecurityDevice::factory(),
            'bulk_dispatch_id' => BulkDispatch::factory(),
            'reason' => fake()->sentence(),
            'replaced_by' => User::factory(),
        ];
    }
}
