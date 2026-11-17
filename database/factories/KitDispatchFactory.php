<?php

namespace Database\Factories;

use App\Models\KitDispatch;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KitDispatch>
 */
class KitDispatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'receiver_id' => Worker::factory(),
            'created_by' => User::factory(),
        ];
    }
}
