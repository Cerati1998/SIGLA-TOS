<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public const MAIN_BRANCH_CODE = '0001';

    /**
     * Load the only branch of the company, unless there is one already: from then on they are managed on their screen.
     */
    public function run(): void
    {
        if (Branch::withTrashed()->exists()) {
            return;
        }

        Branch::query()->create([
            'company_id' => Company::query()->value('id'),
            'code' => self::MAIN_BRANCH_CODE,
            'name' => 'Depot Paita - Macromar',
            'ubigeo' => '200501',
            'address' => 'ZONA INDUSTRIAL II COMPLEMENTARIA INDUSTRIAL MZ.I SUBLOTE.01-A-03',
        ]);
    }
}
