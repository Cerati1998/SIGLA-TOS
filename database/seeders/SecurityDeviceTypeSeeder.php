<?php

namespace Database\Seeders;

use App\Enums\SecurityDeviceKind;
use App\Models\SecurityDeviceType;
use Illuminate\Database\Seeder;

class SecurityDeviceTypeSeeder extends Seeder
{
    /**
     * The initial security device types, the ones shown in the H12 and H13 designs, asked by the user on 2026-11-12.
     * The internal and the external tape are one type, the inspection tape (corrected by the user on 2026-11-16).
     *
     * @var array<string, SecurityDeviceKind>
     */
    public const array TYPES = [
        'Cinta de inspección' => SecurityDeviceKind::Tape,
        'Cinta puerta empty' => SecurityDeviceKind::Tape,
        'Cinta puerta full' => SecurityDeviceKind::Tape,
        'Sello botella' => SecurityDeviceKind::Seal,
        'Sello cable' => SecurityDeviceKind::Seal,
        'Sello plástico' => SecurityDeviceKind::Seal,
        'Precinto de naviera' => SecurityDeviceKind::ContainerSeal,
        'Formato de inspección' => SecurityDeviceKind::Other,
    ];

    /**
     * The initial types whose quantity in a kit type is split between the internal and the external assignment.
     *
     * @var array<int, string>
     */
    public const array SPLIT_INTERNAL_EXTERNAL = [
        'Cinta de inspección',
    ];

    /**
     * Load the initial security device types. A type that already exists, even deactivated, is left as it is.
     */
    public function run(): void
    {
        foreach (self::TYPES as $name => $kind) {
            SecurityDeviceType::withTrashed()->firstOrCreate(['name' => $name], [
                'kind' => $kind,
                'splits_internal_external' => in_array($name, self::SPLIT_INTERNAL_EXTERNAL, true),
            ]);
        }
    }
}
