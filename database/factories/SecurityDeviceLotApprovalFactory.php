<?php

namespace Database\Factories;

use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceLotApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SecurityDeviceLotApproval>
 */
class SecurityDeviceLotApprovalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'security_device_lot_id' => SecurityDeviceLot::factory(),
            'correlative_start' => 200,
            'correlative_end' => 249,
            'approved_count' => 50,
            'approved_by' => User::factory(),
        ];
    }
}
