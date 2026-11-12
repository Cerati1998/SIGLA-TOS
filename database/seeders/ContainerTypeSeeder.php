<?php

namespace Database\Seeders;

use App\Models\ContainerType;
use Illuminate\Database\Seeder;

class ContainerTypeSeeder extends Seeder
{
    /**
     * The container types given by the user on 2026-11-12, as code => [ISO code, description, is reefer, length, width, height].
     *
     * @var array<string, array{0: string, 1: string, 2: bool, 3: string, 4: string, 5: string}>
     */
    public const array TYPES = [
        '22GP' => ['22G1', "20'-ST Standard", false, '6.06', '2.44', '2.59'],
        '42GP' => ['42G1', "40'-ST Standard", false, '12.19', '2.44', '2.59'],
        '40HC' => ['45G1', "40'-HQ High Cube", false, '12.19', '2.44', '2.89'],
        '45HC' => ['L5G1', "45'-HQ High Cube", false, '13.72', '2.44', '2.89'],
        '20HT' => ['22U6', "20'-HT Hardtop", false, '6.06', '2.44', '2.59'],
        '40HT' => ['42U6', "40'-HT Hardtop", false, '12.19', '2.44', '2.59'],
        '40HTHC' => ['45U6', "40'-HTHC Hardtop High Cube", false, '12.19', '2.44', '2.89'],
        '20OT' => ['22U1', "20'-OT Open Top", false, '6.06', '2.44', '2.59'],
        '40OT' => ['42U1', "40'-OT Open Top", false, '12.19', '2.44', '2.59'],
        '40OTHC' => ['45U1', "40'-OTHC Open Top High Cube", false, '12.19', '2.44', '2.89'],
        '20RF' => ['22R1', "20'-RF Reefer", true, '5.45', '2.29', '2.27'],
        '40RF' => ['45R1', "40'-RFHC Reefer High Cube", true, '11.56', '2.29', '2.59'],
        '20FR' => ['22P3', "20'-FR Flat Rack", false, '5.70', '2.36', '2.24'],
        '40FR' => ['42P3', "40'-FR Flat Rack", false, '12.06', '2.44', '2.28'],
        '40PF' => ['45P3', "40'-PF Platform", false, '12.19', '2.44', '0.65'],
    ];

    /**
     * Load the container types. A type that already exists, even deactivated, is left as it is.
     */
    public function run(): void
    {
        foreach (self::TYPES as $code => [$isoCode, $description, $isReefer, $length, $width, $height]) {
            ContainerType::withTrashed()->firstOrCreate(['code' => $code], [
                'iso_code' => $isoCode,
                'description' => $description,
                'is_reefer' => $isReefer,
                'length' => $length,
                'width' => $width,
                'height' => $height,
            ]);
        }
    }
}
