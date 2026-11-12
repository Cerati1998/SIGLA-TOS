<?php

namespace App\Actions\Drivers;

use App\Models\Carrier;
use App\Models\CarrierDriver;
use App\Models\Driver;
use App\Models\Identity;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddDriverToCarrier
{
    /**
     * Add the driver with the given DNI to the carrier. A driver that is not registered yet is created with the given
     * data; one that is registered keeps its data. A link that was deactivated is reactivated.
     *
     * @param  array{full_name: string, license: string}  $newDriver  The data used only when the DNI is not registered yet.
     *
     * @throws ValidationException When the driver is already active in the carrier.
     */
    public function handle(Carrier $carrier, string $documentNumber, array $newDriver): CarrierDriver
    {
        return DB::transaction(function () use ($carrier, $documentNumber, $newDriver): CarrierDriver {
            $driver = Driver::withTrashed()
                ->where('identity_id', Identity::DNI)
                ->where('document_number', $documentNumber)
                ->first();

            $driver ??= Driver::query()->create([
                'identity_id' => Identity::DNI,
                'document_number' => $documentNumber,
                ...$newDriver,
            ]);

            $link = CarrierDriver::withTrashed()->firstOrNew(['carrier_id' => $carrier->id, 'driver_id' => $driver->id]);

            if ($link->exists && ! $link->trashed()) {
                throw ValidationException::withMessages(['form.documentNumber' => 'Este conductor ya está en este transportista.']);
            }

            if ($link->trashed()) {
                $link->restore();
            } else {
                $link->save();
            }

            return $link;
        });
    }
}
