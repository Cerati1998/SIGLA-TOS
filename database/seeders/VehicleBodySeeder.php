<?php

namespace Database\Seeders;

use App\Models\VehicleBody;
use Illuminate\Database\Seeder;

class VehicleBodySeeder extends Seeder
{
    /**
     * The initial vehicle bodies, given by the user on 2026-11-11.
     *
     * @var list<string>
     */
    public const array BODIES = [
        'Remolcador 4x2',
        'Remolcador 6x2',
        'Remolcador 6x4',
        'Plataforma',
        'Plataforma abierta',
        'Containera',
        'Portacontenedor extensible',
        'Cisterna',
        'Chasis Gooseneck',
    ];

    /**
     * Load the initial vehicle bodies in capitals. A body that already exists, even deactivated, is left as it is.
     */
    public function run(): void
    {
        foreach (self::BODIES as $name) {
            VehicleBody::withTrashed()->firstOrCreate(['name' => VehicleBody::normalizeName($name)]);
        }
    }
}
