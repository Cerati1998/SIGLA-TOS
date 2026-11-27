<?php

namespace Database\Seeders;

use App\Models\Identity;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    /**
     * The RUC of AUTOSAFE SAC, the company behind the Hunter brand of GPS devices (given by the user on 2026-11-27).
     */
    public const string HUNTER_RUC = '20504292968';

    /**
     * The initial providers with RUC. The RUC is a field and not the key of the list, because PHP would turn a numeric
     * key into an integer.
     *
     * @var list<array{document_number: string, business_name: string, address: string|null}>
     */
    public const array PROVIDERS = [
        [
            'document_number' => self::HUNTER_RUC,
            'business_name' => 'AUTOSAFE SAC',
            'address' => 'AV. JOSE PARDO NRO. 434 INT. 1301 LIMA LIMA MIRAFLORES',
        ],
    ];

    /**
     * Load the initial providers. A provider whose RUC already exists, even a deactivated one, is left as it is: from
     * then on it is managed on its screen.
     */
    public function run(): void
    {
        foreach (self::PROVIDERS as $provider) {
            Provider::withTrashed()->firstOrCreate(
                ['identity_id' => Identity::RUC, 'document_number' => $provider['document_number']],
                ['business_name' => $provider['business_name'], 'address' => $provider['address']],
            );
        }
    }
}
