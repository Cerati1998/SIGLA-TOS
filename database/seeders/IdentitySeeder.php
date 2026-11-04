<?php

namespace Database\Seeders;

use App\Models\Identity;
use Illuminate\Database\Seeder;

class IdentitySeeder extends Seeder
{
    /**
     * Load the document types of SUNAT catalog 06.
     */
    public function run(): void
    {
        $identities = [
            ['-', 'Sin documento'],
            ['0', 'DOC.TRIB.'],
            ['1', 'DNI'],
            ['2', 'Código IICL'],
            ['4', 'Carnet de extranjería'],
            ['6', 'RUC'],
            ['7', 'Pasaporte'],
            ['A', 'Cédula Diplomática'],
            ['B', 'DOC.IDENT.EXTRANJERO'],
            ['C', 'TIN'],
            ['D', 'IN'],
            ['E', 'TAM'],
        ];

        foreach ($identities as [$id, $description]) {
            Identity::query()->updateOrCreate(['id' => $id], ['description' => $description]);
        }
    }
}
