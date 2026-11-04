<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public const MAIN_BRANCH_CODE = '0000';

    /**
     * Load the branches of the company.
     */
    public function run(): void
    {
        $branches = [
            [
                'code' => '0001',
                'name' => 'Depot Paita - Macromar',
                'ubigeo' => '200501',
                'address' => 'ZONA INDUSTRIAL II COMPLEMENTARIA INDUSTRIAL MZ.I SUBLOTE.01-A-03',
            ],
            [
                'code' => self::MAIN_BRANCH_CODE,
                'name' => 'Sede Principal',
                'ubigeo' => '200501',
                'address' => 'MZA. H1 LOTE 10-A A.H. CIUDAD ROJA DEL PESCADOR',
            ],
        ];

        foreach ($branches as $branch) {
            Branch::query()->updateOrCreate(['code' => $branch['code']], $branch);
        }
    }
}
