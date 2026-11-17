<?php

namespace Database\Seeders;

use App\Models\ShippingLine;
use Illuminate\Database\Seeder;

class ShippingLineSeeder extends Seeder
{
    /**
     * The initial shipping lines given by the user on 2026-11-16, as code => name.
     *
     * @var array<string, string>
     */
    public const array LINES = [
        'MAEU' => 'Maersk Line',
        'MSCU' => 'Mediterranean Shipping Company (MSC)',
        'CMDU' => 'CMA CGM Group',
        'COSU' => 'COSCO Shipping Lines',
        'HLCU' => 'Hapag-Lloyd',
        'EVEG' => 'Evergreen Marine',
        'ONEY' => 'Ocean Network Express (ONE)',
        'YMLU' => 'Yang Ming Marine Transport',
        'HMMU' => 'Hyundai Merchant Marine (HMM)',
        'ZIMU' => 'ZIM Integrated Shipping Services',
        'WANH' => 'Wan Hai Lines',
        'SEAL' => 'Sealand (Maersk)',
        'KMTL' => 'KMT Line',
        'SEAT' => 'Seatrade',
        'HAMB' => 'Hamburg Süd',
        'OOLU' => 'OOCL',
        'NYKL' => 'NYK Line',
        'KLEL' => 'K Line',
        'UASC' => 'United Arab Shipping Company',
        'APLU' => 'APL',
        'EMCL' => 'Emirates Shipping Line',
        'HEUN' => 'Heung-A Line',
        'DLMT' => 'Del Monte Shipping',
        'CHQT' => 'Chiquita Brands International',
        'STFR' => 'Star Fruit',
        'FCFR' => 'FCF Reefer',
        'GREX' => 'Great White Fleet (Del Monte)',
        'BANF' => 'Banana Fleet',
        'DOLP' => 'Dole Ocean Cargo Express',
    ];

    /**
     * Load the initial shipping lines. A line whose code already exists, even deactivated, is left as it is.
     */
    public function run(): void
    {
        foreach (self::LINES as $code => $name) {
            ShippingLine::withTrashed()->firstOrCreate(['code' => $code], ['name' => $name]);
        }
    }
}
