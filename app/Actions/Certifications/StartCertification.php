<?php

namespace App\Actions\Certifications;

use App\Enums\GateOutRequestSource;
use App\Enums\VehicleKind;
use App\Models\Carrier;
use App\Models\CarrierDriver;
use App\Models\Certification;
use App\Models\Driver;
use App\Models\Identity;
use App\Models\Kit;
use App\Models\ShippingLine;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\YardZone;
use App\Services\ShippingLineRegistry;
use App\Services\Sigla1LookupResult;
use App\Services\VehicleRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * @phpstan-import-type GateOutRequest from Sigla1LookupResult
 * @phpstan-import-type Vehicle from Sigla1LookupResult as Sigla1Vehicle
 */
class StartCertification
{
    public function __construct(
        private FindKitToCertify $findKit,
        private DistributeKitDevices $distributeKitDevices,
        private ShippingLineRegistry $shippingLines,
        private VehicleRegistry $vehicles,
    ) {}

    /**
     * Start the certification of the unit of a gate out request with a dispatched kit (H17). Everything is saved or
     * nothing is (criterion 4). The certification keeps only the reference, the booking and the container of the
     * request, which comes from SIGLA 1.0 (source `api`, T81), and links the shipping line, the carrier, the driver and the vehicles, registering the ones that are
     * not in their catalogs yet (criterion 3). The shipping line is not registered: it is the active line squared
     * with the request or chosen by the inspector, and the line remembers how SIGLA 1.0 writes it (T74). The kit
     * stays Despachado.
     *
     * @param  GateOutRequest  $gateOutRequest
     *
     * @throws ValidationException When the request already has a certification or the kit can no longer be used.
     */
    public function handle(array $gateOutRequest, ShippingLine $shippingLine, Kit $kit, ?string $inspectionSheetCode, YardZone $yardZone, User $inspector): Certification
    {
        return DB::transaction(function () use ($gateOutRequest, $shippingLine, $kit, $inspectionSheetCode, $yardZone, $inspector): Certification {
            $kit = Kit::query()->with('shippingLine')->lockForUpdate()->findOrFail($kit->id);

            if (Certification::query()->whereGateOutRequest(GateOutRequestSource::Api, $gateOutRequest['reference'])->exists()) {
                throw ValidationException::withMessages(['form.booking' => 'Esta solicitud ya tiene una certificación.']);
            }

            $rejectionReason = $this->findKit->rejectionReason($kit, $shippingLine);

            if ($rejectionReason !== null) {
                throw ValidationException::withMessages(['form.pouchCode' => $rejectionReason]);
            }

            $this->shippingLines->remember($shippingLine, $gateOutRequest['shipping_line']);

            $carrier = $this->carrier($gateOutRequest['carrier']);
            $driver = $this->driver($gateOutRequest['driver'], $carrier);

            $certification = Certification::query()->create([
                'source' => GateOutRequestSource::Api,
                'gate_out_request_reference' => $gateOutRequest['reference'],
                'booking' => (string) $gateOutRequest['booking'],
                'container' => (string) $gateOutRequest['container'],
                'client' => $gateOutRequest['client']['name'],
                'product' => $gateOutRequest['product'],
                'shipping_line_id' => $shippingLine->id,
                'carrier_id' => $carrier?->id,
                'driver_id' => $driver?->id,
                'tractor_id' => $this->vehicle($gateOutRequest['tractor'], VehicleKind::Tractor)?->id,
                'semi_trailer_id' => $this->vehicle($gateOutRequest['semi_trailer'], VehicleKind::SemiTrailer)?->id,
                'kit_id' => $kit->id,
                'inspection_sheet_code' => $inspectionSheetCode,
                'yard_zone_id' => $yardZone->id,
                'certified_by' => $inspector->id,
                'started_at' => now(),
            ]);

            $this->copySteps($certification, $kit);

            return $certification;
        });
    }

    /**
     * Copy the active sections into the certification, with the devices of the kit the system places in each one
     * (H18, criterion 3). The kit already fits: it was checked with the other reasons to reject it.
     */
    private function copySteps(Certification $certification, Kit $kit): void
    {
        foreach ($this->distributeKitDevices->handle($kit)['steps'] as $position => $step) {
            $certification->steps()
                ->create([
                    'certification_section_id' => $step['section']->id,
                    'type' => $step['section']->type,
                    'position' => $position + 1,
                ])
                ->devices()
                ->attach($step['deviceIds']);
        }
    }

    /**
     * Get the carrier with the RUC of the request, registering it when it is not in the catalog. Without a RUC or a
     * name there is nothing to link.
     *
     * @param  array{document_number: string|null, name: string|null}  $data
     */
    private function carrier(array $data): ?Carrier
    {
        if ($data['document_number'] === null || $data['name'] === null) {
            return null;
        }

        return Carrier::withTrashed()
            ->where('identity_id', Identity::RUC)
            ->where('document_number', $data['document_number'])
            ->first()
            ?? Carrier::query()->create([
                'identity_id' => Identity::RUC,
                'document_number' => $data['document_number'],
                'business_name' => $data['name'],
            ]);
    }

    /**
     * Get the driver with the DNI of the request, registering it when it is not in the catalog, and link it with the
     * carrier of the same request when it is not linked yet (T45). Without a DNI, a name or a license there is
     * nothing to link.
     *
     * @param  array{name: string|null, license: string|null, document_number: string|null}  $data
     */
    private function driver(array $data, ?Carrier $carrier): ?Driver
    {
        if ($data['document_number'] === null || $data['name'] === null || $data['license'] === null) {
            return null;
        }

        $driver = Driver::withTrashed()
            ->where('identity_id', Identity::DNI)
            ->where('document_number', $data['document_number'])
            ->first()
            ?? Driver::query()->create([
                'identity_id' => Identity::DNI,
                'document_number' => $data['document_number'],
                'full_name' => $data['name'],
                'license' => mb_strtoupper($data['license']),
            ]);

        if ($carrier !== null) {
            $link = CarrierDriver::withTrashed()->firstOrNew(['carrier_id' => $carrier->id, 'driver_id' => $driver->id]);

            if ($link->trashed()) {
                $link->restore();
            } elseif (! $link->exists) {
                $link->save();
            }
        }

        return $driver;
    }

    /**
     * Get the vehicle with the plate of the request, registering it when it is not in the catalog. When SIGLA 1.0
     * does not send its plate, brand or body, there is nothing to link.
     *
     * @param  Sigla1Vehicle  $data
     */
    private function vehicle(array $data, VehicleKind $kind): ?Vehicle
    {
        try {
            return $this->vehicles->findOrRegister($data, $kind);
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}
