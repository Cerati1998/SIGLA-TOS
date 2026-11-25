<?php

namespace App\Actions\Kits;

use App\Actions\SecurityDevices\FindSkippedSecurityDevice;
use App\Enums\KitAssignment;
use App\Enums\SecurityDeviceStatus;
use App\Models\Kit;
use App\Models\SecurityDevice;
use Illuminate\Support\Facades\DB;

class AddSecurityDeviceToKit
{
    public function __construct(private FindSkippedSecurityDevice $findSkippedDevice) {}

    /**
     * Add the device with the given serial number to a slot of the kit, and update the status of both (H14). Return
     * the reason it was not added, to show it on screen, or null when it was added.
     */
    public function handle(Kit $kit, string $slotKey, string $serialNumber): ?string
    {
        return DB::transaction(function () use ($kit, $slotKey, $serialNumber): ?string {
            $kit = Kit::query()->lockForUpdate()->findOrFail($kit->id);

            if (! $kit->status->contentsCanChange()) {
                return 'El kit ya se despachó: su contenido no cambia.';
            }

            $slot = collect($kit->slots())->firstWhere('key', $slotKey);

            if ($slot === null) {
                return 'Ese campo no es de este kit.';
            }

            $device = $this->device($serialNumber, $slot['securityDeviceTypeId']) ?? $this->device($serialNumber);

            $reason = $device === null ? 'No encontramos un dispositivo con esa serie.' : $this->rejectionReason($kit, $slot, $device);

            if ($reason !== null || $device === null) {
                return $reason;
            }

            $skipped = $this->findSkippedDevice->handle([$device->id]);

            if ($skipped !== null) {
                return FindSkippedSecurityDevice::message($skipped);
            }

            $kit->devices()->attach($device->id, ['assignment' => $slot['assignment']?->value]);
            $device->update(['status' => SecurityDeviceStatus::InKit]);
            $kit->syncStatus();

            return null;
        });
    }

    /**
     * Find the device with the serial number, locked for the change, of the type of the slot when given: two types that
     * do not use a series can share a serial number (T62).
     */
    private function device(string $serialNumber, ?int $securityDeviceTypeId = null): ?SecurityDevice
    {
        return SecurityDevice::query()
            ->with(['lot.securityDeviceType', 'lot.announcement.shippingLine', 'kits'])
            ->where('serial_number', $serialNumber)
            ->when($securityDeviceTypeId, fn ($query, int $typeId) => $query->ofType($typeId))
            ->lockForUpdate()
            ->first();
    }

    /**
     * Get why the device cannot go in the slot, in the order the warehouse needs to know it, or null when it can.
     *
     * @param  array{key: string, securityDeviceTypeId: int, name: string, assignment: KitAssignment|null, quantity: int, added: int}  $slot
     */
    private function rejectionReason(Kit $kit, array $slot, SecurityDevice $device): ?string
    {
        $currentKit = $device->kits->first();

        if ($currentKit !== null) {
            return $currentKit->is($kit) ? 'Ya está en este kit.' : sprintf('Ya está en el kit %s.', $currentKit->pouch_code);
        }

        if ($device->status === SecurityDeviceStatus::Inactive) {
            return 'No está activado para despacho. Actívalo desde su lote antes de agregarlo.';
        }

        if ($device->status !== SecurityDeviceStatus::Available) {
            return sprintf('Está %s; solo se agregan dispositivos disponibles.', $device->status->label());
        }

        $lot = $device->lot;

        if ($lot->announcement->shipping_line_id !== $kit->shipping_line_id) {
            return sprintf('Es de la naviera %s; este kit es de %s.', $lot->announcement->shippingLine->code, $kit->shippingLine->code);
        }

        $deviceTypeName = $lot->securityDeviceType->name;

        if (! $kit->securityDeviceTypes->contains('id', $lot->security_device_type_id)) {
            return sprintf('El tipo %s no va en este tipo de kit.', $deviceTypeName);
        }

        if ($lot->security_device_type_id !== $slot['securityDeviceTypeId']) {
            return sprintf('Es %s, no %s. Escanéalo en su campo.', $deviceTypeName, $slot['name']);
        }

        if ($slot['added'] >= $slot['quantity']) {
            return sprintf('El kit ya tiene %d de %d en %s.', $slot['added'], $slot['quantity'], self::slotLabel($slot));
        }

        return null;
    }

    /**
     * Get the name of a slot shown on screen: the device type, and its side when it is split.
     *
     * @param  array{name: string, assignment: KitAssignment|null}  $slot
     */
    public static function slotLabel(array $slot): string
    {
        return $slot['assignment'] === null ? $slot['name'] : $slot['name'].' · '.$slot['assignment']->label();
    }
}
