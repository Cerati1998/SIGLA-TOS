<?php

namespace Database\Seeders;

use App\Actions\Drivers\AddDriverToCarrier;
use App\Actions\KitDispatches\DispatchKits;
use App\Actions\Kits\AddSecurityDeviceToKit;
use App\Actions\Kits\CreateKit;
use App\Actions\SecurityDevices\ApproveSecurityDeviceLotRange;
use App\Actions\SecurityDevices\ReceiveSecurityDeviceLot;
use App\Enums\CertificationSectionType;
use App\Enums\SecurityDeviceStatus;
use App\Enums\VehicleKind;
use App\Models\Carrier;
use App\Models\CertificationSection;
use App\Models\ContainerType;
use App\Models\Kit;
use App\Models\KitType;
use App\Models\Provider;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBody;
use App\Models\Worker;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Sample data to try the modules locally, up to the dispatch of kits: providers, workers, sections, carriers with their
 * drivers and vehicles, lots received and activated, kit types, kits Armados and Armando, and two dispatches.
 * It goes through the same actions as the screens. Run it on its own, never in production:
 * php artisan db:seed --class=LocalTestingSeeder
 */
class LocalTestingSeeder extends Seeder
{
    /**
     * The document of the first sample provider: if it exists, the sample data was loaded already.
     */
    public const string MARKER_DOCUMENT = '20999999901';

    private User $user;

    private int $nextPouch = 1;

    /**
     * Load the sample data.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('LocalTestingSeeder solo se usa en un entorno local.');
        }

        if (Provider::withTrashed()->where('document_number', self::MARKER_DOCUMENT)->exists()) {
            $this->command->warn('Los datos de prueba ya estaban cargados: no se agregó nada.');

            return;
        }

        $this->user = User::query()->orderBy('id')->firstOrFail();

        $providers = $this->providers();
        $workers = $this->workers($providers);
        $this->certificationSections();
        $this->carriers();

        $maersk = ShippingLine::query()->where('code', 'MAEU')->firstOrFail();
        $hapagLloyd = ShippingLine::query()->where('code', 'HLCU')->firstOrFail();
        $containerSeal = SecurityDeviceType::query()->where('name', 'Precinto de naviera')->firstOrFail();
        $bottleSeal = SecurityDeviceType::query()->where('name', 'Sello botella')->firstOrFail();
        $inspectionTape = SecurityDeviceType::query()->where('name', 'Cinta de inspección')->firstOrFail();

        $this->lot($maersk, $providers[0], $containerSeal, 'MSK', 1, 60);
        $this->lot($maersk, $providers[0], $bottleSeal, 'SB', 1001, 1120);
        $this->lot($maersk, $providers[1], $inspectionTape, 'CIN', 1, 120);
        $this->lot($hapagLloyd, $providers[1], $bottleSeal, 'HLS', 1, 60);

        $containerType = ContainerType::query()->orderBy('id')->firstOrFail();
        $reeferKit = KitType::factory()
            ->carrying($containerSeal, 1)
            ->carrying($bottleSeal, 2)
            ->hasAttached($inspectionTape, ['quantity' => 3, 'internal_quantity' => 1, 'external_quantity' => 2], 'securityDeviceTypes')
            ->create(['name' => 'Kit de seguridad reefer', 'container_type_id' => $containerType->id]);
        $sealsKit = KitType::factory()
            ->carrying($bottleSeal, 2)
            ->create(['name' => 'Kit de cambio de sellos', 'container_type_id' => $containerType->id]);

        $reeferKits = array_map(fn (): Kit => $this->kit($reeferKit, $maersk), range(1, 6));
        $this->kit($reeferKit, $maersk, leaveMissing: 1);
        $this->kit($reeferKit, $maersk, leaveMissing: 6);
        $sealsKits = array_map(fn (): Kit => $this->kit($sealsKit, $hapagLloyd), range(1, 4));

        $dispatchKits = app(DispatchKits::class);
        $dispatchKits->handle($workers[0], [$reeferKits[0]->id, $reeferKits[1]->id], $this->user);
        $dispatchKits->handle($workers[1], [$sealsKits[0]->id], $this->user);

        $this->command->info('Datos de prueba cargados: 12 kits (7 Armados por despachar, 2 Armando y 3 ya despachados en 2 despachos).');
    }

    /**
     * Create three providers, the first one with the marker document.
     *
     * @return list<Provider>
     */
    private function providers(): array
    {
        return [
            Provider::factory()->create(['document_number' => self::MARKER_DOCUMENT, 'business_name' => 'Precintos del Norte S.A.C.']),
            Provider::factory()->create(['business_name' => 'Seguridad Portuaria E.I.R.L.']),
            Provider::factory()->create(['business_name' => 'Cintas y Sellos del Perú S.A.']),
        ];
    }

    /**
     * Create four own workers and two of a provider.
     *
     * @param  list<Provider>  $providers
     * @return list<Worker>
     */
    private function workers(array $providers): array
    {
        $own = Worker::factory()->count(4)->sequence(
            ['full_name' => 'Juan Pérez Ramos', 'position' => 'Inspector'],
            ['full_name' => 'María Quispe Flores', 'position' => 'Inspectora'],
            ['full_name' => 'Carlos Sánchez Díaz', 'position' => 'Checker de lavado'],
            ['full_name' => 'Rosa Chávez Vega', 'position' => 'Supervisora'],
        )->create(['is_own' => true]);

        $fromProvider = Worker::factory()->count(2)->sequence(
            ['full_name' => 'Luis Torres Mendoza', 'provider_id' => $providers[1]->id],
            ['full_name' => 'Ana Rojas Castillo', 'provider_id' => $providers[2]->id],
        )->create(['is_own' => false, 'position' => 'Técnico']);

        return [...$own->all(), ...$fromProvider->all()];
    }

    /**
     * Create the sections of the container, external and internal.
     */
    private function certificationSections(): void
    {
        $sections = [
            ['Lado derecho', CertificationSectionType::External],
            ['Lado izquierdo', CertificationSectionType::External],
            ['Puertas', CertificationSectionType::External],
            ['Techo', CertificationSectionType::External],
            ['Piso', CertificationSectionType::Internal],
            ['Panel frontal', CertificationSectionType::Internal],
        ];

        foreach ($sections as $order => [$name, $type]) {
            CertificationSection::factory()->create(['name' => $name, 'type' => $type, 'sort_order' => $order + 1]);
        }
    }

    /**
     * Create two carriers with their drivers, and their tractors and semi-trailers.
     */
    private function carriers(): void
    {
        $addDriver = app(AddDriverToCarrier::class);
        $vehicleBodyId = VehicleBody::query()->orderBy('id')->value('id') ?? VehicleBody::factory()->create()->id;

        foreach (['Transportes Andinos S.A.C.', 'Logística del Pacífico S.R.L.'] as $index => $businessName) {
            $carrier = Carrier::factory()->create(['business_name' => $businessName]);

            foreach (range(1, 2) as $driverNumber) {
                $documentNumber = sprintf('7%07d', $index * 10 + $driverNumber);
                $addDriver->handle($carrier, $documentNumber, [
                    'full_name' => mb_strtoupper(fake()->lastName().' '.fake()->lastName().' '.fake()->firstName()),
                    'license' => 'Q'.$documentNumber,
                ]);
            }
        }

        Vehicle::factory()->count(2)->create(['kind' => VehicleKind::Tractor, 'vehicle_body_id' => $vehicleBodyId]);
        Vehicle::factory()->count(2)->create(['kind' => VehicleKind::SemiTrailer, 'vehicle_body_id' => $vehicleBodyId]);
    }

    /**
     * Receive a lot and activate it for dispatch (H29), all but its last ten devices, which stay inactive to try the
     * activation.
     */
    private function lot(ShippingLine $shippingLine, Provider $provider, SecurityDeviceType $type, string $prefix, int $start, int $end): SecurityDeviceLot
    {
        $announcement = new SecurityDeviceAnnouncement([
            'shipping_line_id' => $shippingLine->id,
            'provider_id' => $provider->id,
            'security_device_type_id' => $type->id,
            'series_prefix' => $prefix,
            'min_digits' => 7,
            'series_start' => $start,
            'series_end' => $end,
            'series_suffix' => null,
        ]);

        $lot = app(ReceiveSecurityDeviceLot::class)->handle($announcement, [
            'received_at' => now()->subDays(3)->toDateString(),
            'dispatch_number' => sprintf('T001-%06d', $start),
            'notes' => null,
        ], $this->user) ?? throw new RuntimeException("No se pudo recibir el lote {$prefix}.");

        app(ApproveSecurityDeviceLotRange::class)->handle($lot, $start, $end - 10, $this->user);

        return $lot;
    }

    /**
     * Create a kit and scan its devices, leaving the given number of them missing.
     */
    private function kit(KitType $kitType, ShippingLine $shippingLine, int $leaveMissing = 0): Kit
    {
        $kit = app(CreateKit::class)->handle([
            'pouchCode' => sprintf('KIT-%06d', $this->nextPouch++),
            'kitTypeId' => $kitType->id,
            'shippingLineId' => $shippingLine->id,
        ], $this->user);

        $addDevice = app(AddSecurityDeviceToKit::class);
        $slots = $kit->slots();
        $toAdd = array_sum(array_column($slots, 'quantity')) - $leaveMissing;

        foreach ($slots as $slot) {
            for ($added = 0; $added < $slot['quantity'] && $toAdd > 0; $added++, $toAdd--) {
                $serialNumber = SecurityDevice::query()
                    ->where('status', SecurityDeviceStatus::Available)
                    ->whereHas('lot.announcement', fn ($query) => $query
                        ->where('shipping_line_id', $shippingLine->id)
                        ->where('security_device_type_id', $slot['securityDeviceTypeId']))
                    ->orderBy('serial_number')
                    ->value('serial_number');

                $reason = $addDevice->handle($kit, $slot['key'], (string) $serialNumber);

                if ($reason !== null) {
                    throw new RuntimeException("No se pudo armar {$kit->pouch_code}: {$reason}");
                }
            }
        }

        return $kit->refresh();
    }
}
