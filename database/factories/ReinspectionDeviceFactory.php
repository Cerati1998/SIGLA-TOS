<?php

namespace Database\Factories;

use App\Models\CertificationStep;
use App\Models\Reinspection;
use App\Models\ReinspectionDevice;
use App\Models\SecurityDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReinspectionDevice>
 */
class ReinspectionDeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reinspection_id' => Reinspection::factory(),
            'certification_step_id' => CertificationStep::factory(),
            'security_device_id' => SecurityDevice::factory(),
        ];
    }
}
