<?php

namespace App\Actions\BulkDispatches;

use App\Actions\SecurityDevices\FindSkippedSecurityDevice;
use App\Enums\SecurityDeviceStatus;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceType;

class FindDeviceToBulkDispatch
{
    public function __construct(private FindSkippedSecurityDevice $findSkippedDevice) {}

    /**
     * Find the device with the scanned or written serial number, or get why it cannot be dispatched in bulk, to show
     * it on screen. When given, only the devices of the type are looked up (T62). The devices already in the dispatch
     * count as taken when its shipping line dispatches in order (T82).
     *
     * @param  list<int>  $chosenIds
     */
    public function handle(string $serialNumber, ?SecurityDeviceType $type = null, array $chosenIds = []): SecurityDevice|string
    {
        $device = $this->find($serialNumber, $type, ['lot.securityDeviceType', 'kits', 'bulkDispatches']);

        if (is_string($device)) {
            return $device;
        }

        $reason = $this->rejectionReason($device);

        if ($reason !== null) {
            return $reason;
        }

        $skipped = $this->findSkippedDevice->handle([$device->id], $chosenIds);

        return $skipped === null ? $device : FindSkippedSecurityDevice::message($skipped);
    }

    /**
     * Find the only device with the serial number, of the type when given, or say that there is none or that the serial
     * number exists in more than one type, which happens with the types that do not use a series (T62).
     *
     * @param  list<string>  $with
     */
    public function find(string $serialNumber, ?SecurityDeviceType $type = null, array $with = ['lot.securityDeviceType']): SecurityDevice|string
    {
        $devices = SecurityDevice::query()
            ->with($with)
            ->where('serial_number', $serialNumber)
            ->when($type, fn ($query, SecurityDeviceType $type) => $query->ofType($type->id))
            ->limit(2)
            ->get();

        if ($devices->count() > 1) {
            return sprintf('La serie %s existe en más de un tipo. Elige el tipo de dispositivo.', $serialNumber);
        }

        return $devices->first() ?? ($type === null
            ? sprintf('No encontramos un dispositivo con la serie %s.', $serialNumber)
            : sprintf('No encontramos un dispositivo de %s con la serie %s.', $type->name, $serialNumber));
    }

    /**
     * Get why the device cannot be dispatched in bulk, or null when it can: only an Available device, that is, approved
     * by the shipping line, and out of a kit (H30, criterion 8). The device needs its kits and bulk dispatches loaded.
     */
    public function rejectionReason(SecurityDevice $device): ?string
    {
        $kit = $device->kits->first();

        if ($kit !== null) {
            return sprintf('Está en el kit %s. Un dispositivo en un kit no se despacha a granel.', $kit->pouch_code);
        }

        $bulkDispatch = $device->bulkDispatches->first();

        if ($bulkDispatch !== null) {
            return sprintf('Ya está despachado en el despacho %s.', $bulkDispatch->number());
        }

        return match ($device->status) {
            SecurityDeviceStatus::Available => null,
            SecurityDeviceStatus::Inactive => 'No está activado para despacho. Actívalo desde su lote antes de despacharlo.',
            default => sprintf('Está %s; solo se despachan dispositivos disponibles.', $device->status->label()),
        };
    }
}
