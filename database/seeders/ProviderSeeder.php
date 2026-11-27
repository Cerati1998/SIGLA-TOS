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
     * The initial providers with RUC, as document number => [business name, address].
     *
     * @var array<string, array{0: string, 1: string|null}>
     */
    public const array PROVIDERS = [
        self::HUNTER_RUC => ['AUTOSAFE SAC', 'AV. JOSE PARDO NRO. 434 INT. 1301 LIMA LIMA MIRAFLORES'],
    ];

    /**
     * Load the initial providers. A provider whose RUC already exists, even a deactivated one, is left as it is: from
     * then on it is managed on its screen.
     */
    public function run(): void
    {
        foreach (self::PROVIDERS as $documentNumber => [$businessName, $address]) {
            Provider::withTrashed()->firstOrCreate(
                ['identity_id' => Identity::RUC, 'document_number' => $documentNumber],
                ['business_name' => $businessName, 'address' => $address],
            );
        }
    }
}
