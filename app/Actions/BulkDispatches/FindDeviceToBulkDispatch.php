<?php

namespace App\Actions\BulkDispatches;

use App\Enums\SecurityDeviceStatus;
use App\Models\SecurityDevice;

class FindDeviceToBulkDispatch
{
    /**
     * Find the device with the scanned or written serial number, or get why it cannot be dispatched in bulk, to show
     * it on screen.
     */
    public function handle(string $serialNumber): SecurityDevice|string
    {
        $device = SecurityDevice::query()
            ->with(['lot.announcement.securityDeviceType', 'kits', 'bulkDispatches'])
            ->where('serial_number', $serialNumber)
            ->first();

        if ($device === null) {
            return sprintf('No encontramos un dispositivo con la serie %s.', $serialNumber);
        }

        return $this->rejectionReason($device) ?? $device;
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
