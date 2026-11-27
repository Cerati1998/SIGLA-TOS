<?php

namespace Database\Seeders;

use App\Enums\GpsDeviceCategory;
use App\Enums\GpsOpeningType;
use App\Models\GpsDeviceModel;
use App\Models\Identity;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class GpsDeviceModelSeeder extends Seeder
{
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
     * Load the Hunter models in their brand, the active provider with the RUC of AUTOSAFE SAC that ProviderSeeder loads.
     * Without it nothing is loaded. A model that already exists in the brand, even a deactivated one, is left as it is.
     */
    public function run(): void
    {
        $brandId = Provider::query()
            ->where('identity_id', Identity::RUC)
            ->where('document_number', ProviderSeeder::HUNTER_RUC)
            ->value('id');

        if ($brandId === null) {
            $this->command?->warn('No se cargaron los modelos GPS: falta el proveedor AUTOSAFE SAC (RUC '.ProviderSeeder::HUNTER_RUC.') activo. Corre ProviderSeeder o reactívalo.');

            return;
        }

        foreach (self::MODELS as $name => [$category, $openingType, $description]) {
            GpsDeviceModel::withTrashed()->firstOrCreate(
                ['brand_id' => $brandId, 'name' => $name],
                ['category' => $category, 'opening_type' => $openingType, 'description' => $description],
            );
        }
    }
}
