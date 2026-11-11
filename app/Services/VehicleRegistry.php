<?php

namespace App\Services;

use App\Enums\VehicleCategory;
use App\Enums\VehicleKind;
use App\Models\Vehicle;
use App\Models\VehicleBody;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Find a vehicle that SIGLA 1.0 sends in the catalog, or register it there with its data (H23, for H17).
 *
 * @phpstan-import-type Vehicle from Sigla1LookupResult as Sigla1Vehicle
 */
class VehicleRegistry
{
    /**
     * Get the vehicle with the plate that SIGLA 1.0 sends, registering it when it is not in the catalog yet.
     * A body that is not in the catalog is registered too. A vehicle that already exists is returned as it is,
     * even if it is deactivated.
     *
     * @param  Sigla1Vehicle  $data
     *
     * @throws InvalidArgumentException When SIGLA 1.0 sends no plate, no brand or no body.
     */
    public function findOrRegister(array $data, VehicleKind $kind): Vehicle
    {
        $plate = Vehicle::normalizePlate((string) $data['plate']);
        $brand = trim((string) $data['brand']);
        $bodyName = VehicleBody::normalizeName((string) $data['body']);

        if ($plate === '' || $brand === '' || $bodyName === '') {
            throw new InvalidArgumentException('SIGLA 1.0 did not send the plate, the brand or the body of the vehicle.');
        }

        return DB::transaction(function () use ($plate, $brand, $bodyName, $data, $kind): Vehicle {
            $vehicle = Vehicle::withTrashed()->where('plate', $plate)->first();

            if ($vehicle !== null) {
                return $vehicle;
            }

            $body = VehicleBody::withTrashed()->firstOrCreate(['name' => $bodyName]);

            return Vehicle::query()->create([
                'plate' => $plate,
                'brand' => $brand,
                'kind' => $kind,
                'vehicle_body_id' => $body->id,
                'category' => VehicleCategory::fromSigla1($data['category']),
            ]);
        });
    }
}
