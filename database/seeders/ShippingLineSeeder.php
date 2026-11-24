<?php

namespace Database\Seeders;

use App\Models\ShippingLine;
use Illuminate\Database\Seeder;

class ShippingLineSeeder extends Seeder
{
    /**
     * The initial shipping lines, as code => [name, how SIGLA 1.0 writes it]. The first 29 were given by the user on
     * 2026-11-16; SEAB, CSP, SVC and MLS come from the lines of SIGLA 1.0, with its code and their trade name (T74,
     * 2026-11-23). The SIGLA 1.0 name is null when SIGLA 1.0 does not have the line.
     *
     * @var array<string, array{0: string, 1: string|null}>
     */
    public const array LINES = [
        'MAEU' => ['Maersk Line', 'MAERSK LINE'],
        'MSCU' => ['Mediterranean Shipping Company (MSC)', 'MEDITERRANEAN SHIPPING MSC'],
        'CMDU' => ['CMA CGM Group', null],
        'COSU' => ['COSCO Shipping Lines', 'COSCO'],
        'HLCU' => ['Hapag-Lloyd', null],
        'EVEG' => ['Evergreen Marine', null],
        'ONEY' => ['Ocean Network Express (ONE)', 'ONE SHIPPING'],
        'YMLU' => ['Yang Ming Marine Transport', null],
        'HMMU' => ['Hyundai Merchant Marine (HMM)', null],
        'ZIMU' => ['ZIM Integrated Shipping Services', 'ZIM'],
        'WANH' => ['Wan Hai Lines', null],
        'SEAL' => ['Sealand (Maersk)', null],
        'KMTL' => ['KMT Line', null],
        'SEAT' => ['Seatrade', 'SEATRADE'],
        'HAMB' => ['Hamburg Süd', null],
        'OOLU' => ['OOCL', null],
        'NYKL' => ['NYK Line', null],
        'KLEL' => ['K Line', null],
        'UASC' => ['United Arab Shipping Company', null],
        'APLU' => ['APL', null],
        'EMCL' => ['Emirates Shipping Line', null],
        'HEUN' => ['Heung-A Line', null],
        'DLMT' => ['Del Monte Shipping', null],
        'CHQT' => ['Chiquita Brands International', 'CHIQUITA'],
        'STFR' => ['Star Fruit', null],
        'FCFR' => ['FCF Reefer', null],
        'GREX' => ['Great White Fleet (Del Monte)', null],
        'BANF' => ['Banana Fleet', null],
        'DOLP' => ['Dole Ocean Cargo Express', null],
        'SEAB' => ['Seaboard Marine', 'SEABOARD'],
        'CSP' => ['Container Sudamérica Perú', 'CONTAINER SUDAMERICA PERU'],
        'SVC' => ['Servicontainer', 'SERVICONTAINER'],
        'MLS' => ['Macromar Logistics', 'MACROMAR LOGISTICS SAC'],
    ];

    /**
     * Load the initial shipping lines. A line whose code already exists, even deactivated, is left as it is.
     */
    public function run(): void
    {
        foreach (self::LINES as $code => [$name, $sigla1Name]) {
            ShippingLine::withTrashed()->firstOrCreate(['code' => $code], ['name' => $name, 'sigla1_name' => $sigla1Name]);
        }
    }
}
