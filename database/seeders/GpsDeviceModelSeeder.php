<?php

namespace Database\Seeders;

use App\Enums\GpsDeviceCategory;
use App\Enums\GpsOpeningType;
use App\Models\GpsDeviceModel;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class GpsDeviceModelSeeder extends Seeder
{
    /**
     * The text the brand's business name contains. The brand is a provider registered on its screen, with its RUC: this
     * seeder does not create it, because its RUC was not given (asked by the user on 2026-11-27).
     */
    public const string BRAND_NAME = 'HUNTER';

    /**
     * The Hunter models for asset tracking, taken from its website on 2026-11-27, as name => [category, opening, description].
     * They come without monitoring codes: it is not known yet which model each code of the monitoring platform is.
     *
     * @var array<string, array{0: GpsDeviceCategory, 1: GpsOpeningType, 2: string}>
     */
    public const array MODELS = [
        'Hunter Block' => [GpsDeviceCategory::ContainerLock, GpsOpeningType::Remote, 'Candado externo para contenedores, con apertura remota.'],
        'Truck Lock' => [GpsDeviceCategory::DoorLock, GpsOpeningType::Remote, 'Cerradura de las puertas posteriores de unidades pesadas, con apertura remota.'],
        'Candado Inteligente' => [GpsDeviceCategory::LightVehicleLock, GpsOpeningType::Remote, 'Candado para unidades livianas, con apertura remota.'],
        'Block R' => [GpsDeviceCategory::PortableLock, GpsOpeningType::Manual, 'Candado portátil para proteger activos, con apertura manual.'],
        'Hunter Box' => [GpsDeviceCategory::PortableTracker, GpsOpeningType::None, 'GPS portátil para el seguimiento y recupero de activos.'],
        'Track Pro' => [GpsDeviceCategory::PortableTracker, GpsOpeningType::None, 'GPS portátil para monitorear activos fijos y de alto valor.'],
        'Tolva control' => [GpsDeviceCategory::HopperControl, GpsOpeningType::None, 'Control y monitoreo de plataformas de carga.'],
    ];

    /**
     * Load the Hunter models in the active provider whose business name contains «Hunter». Without a single such provider
     * nothing is loaded. A model that already exists in the brand, even a deactivated one, is left as it is.
     */
    public function run(): void
    {
        $brands = Provider::query()->where('business_name', 'like', '%'.self::BRAND_NAME.'%')->limit(2)->pluck('id');

        if ($brands->count() !== 1) {
            $this->command?->warn('No se cargaron los modelos GPS: registra a Hunter en Proveedores (una sola vez y activo) y vuelve a correr este seeder.');

            return;
        }

        foreach (self::MODELS as $name => [$category, $openingType, $description]) {
            GpsDeviceModel::withTrashed()->firstOrCreate(
                ['brand_id' => $brands->first(), 'name' => $name],
                ['category' => $category, 'opening_type' => $openingType, 'description' => $description],
            );
        }
    }
}
