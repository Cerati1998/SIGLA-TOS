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

        $this->call([IdentitySeeder::class, CompanySeeder::class, BranchSeeder::class, VehicleBodySeeder::class, ShippingLineSeeder::class, SecurityDeviceTypeSeeder::class, ContainerTypeSeeder::class, YardZoneSeeder::class, ProviderSeeder::class, GpsDeviceModelSeeder::class]);

        User::firstOrCreate(
            [
                'email' => 'eddy.barranzuela@grupomacromar.com',
            ],
            [
                'name' => 'Eddy Barranzuela',
                'identity_id' => '1',
                'document_number' => '00000000',
                'branch_id' => Branch::query()
                    ->where('code', BranchSeeder::MAIN_BRANCH_CODE)
                    ->value('id'),
                'password' => bcrypt('12345678'),
            ]
        );

        User::firstOrCreate(
            [
                'email' => 'richard.sanchez@grupomacromar.com',
            ],
            [
                'name' => 'Richard Sanchez',
                'identity_id' => '2',
                'document_number' => '00000000',
                'branch_id' => Branch::query()
                    ->where('code', BranchSeeder::MAIN_BRANCH_CODE)
                    ->value('id'),
                'password' => bcrypt('12345678'),
            ]
        );

        // Off so that a fresh database starts without test data (asked by the user on 2026-11-25). To load it locally:
        // php artisan db:seed --class=LocalTestingSeeder
        // if (app()->isLocal()) {
        //     $this->call(LocalTestingSeeder::class);
        // }
    }
}
