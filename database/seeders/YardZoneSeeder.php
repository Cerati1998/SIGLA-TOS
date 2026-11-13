<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\YardZone;
use Illuminate\Database\Seeder;

class YardZoneSeeder extends Seeder
{
    public const DEFAULT_ZONE_NAME = 'Zona certificación';

    public const DEFAULT_ZONE_CAPACITY = 50;

    /**
     * Load the default zone in the main branch, unless there is a zone already: from then on they are managed on their
     * screen.
     */
    public function run(): void
    {
        $branchId = Branch::query()->where('code', BranchSeeder::MAIN_BRANCH_CODE)->value('id');

        if ($branchId === null || YardZone::withTrashed()->exists()) {
            return;
        }

        YardZone::query()->create([
            'branch_id' => $branchId,
            'name' => self::DEFAULT_ZONE_NAME,
            'capacity' => self::DEFAULT_ZONE_CAPACITY,
        ]);
    }
}
