<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public const RUC = '20474360955';

    /**
     * Load the only company of the system.
     */
    public function run(): void
    {
        Company::query()->updateOrCreate(['ruc' => self::RUC], [
            'business_name' => 'MACROMAR LOGISTICS',
            'trade_name' => 'Macromar Logistics',
            'address' => 'Av. Elmer Faucett cdra 30 s/n oficina 406b C.A.C CALLAO Callao, CALLAO 1',
            'ubigeo' => '070101',
        ]);
    }
}
