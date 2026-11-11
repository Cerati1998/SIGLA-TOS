<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([IdentitySeeder::class, CompanySeeder::class, BranchSeeder::class, VehicleBodySeeder::class]);

        User::factory()->create([
            'name' => 'Eddy Barranzuela',
            'email' => 'eddy.barranzuela@grupomacromar.com',
            'identity_id' => '1',
            'document_number' => '00000000',
            'branch_id' => Branch::query()->where('code', BranchSeeder::MAIN_BRANCH_CODE)->value('id'),
        ]);
    }
}
