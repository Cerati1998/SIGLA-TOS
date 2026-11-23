<?php

namespace Database\Factories;

use App\Enums\BulkDispatchReason;
use App\Models\BulkDispatch;
use App\Models\Client;
use App\Models\User;
use App\Models\Worker;
use App\Models\YardZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BulkDispatch>
 */
class BulkDispatchFactory extends Factory
{
    /**
     * Define the model's default state: a sale to a client.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'receiver_id' => Worker::factory(),
            'created_by' => User::factory(),
            'reason' => BulkDispatchReason::Sale,
            'client_id' => Client::factory(),
            'kit_id' => null,
            'yard_zone_id' => YardZone::factory(),
            'notes' => null,
        ];
    }
}
