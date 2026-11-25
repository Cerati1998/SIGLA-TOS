<?php

namespace App\Actions\Kits;

use App\Actions\SecurityDevices\FindSkippedSecurityDevice;
use App\Enums\KitStatus;
use App\Enums\SecurityDeviceKind;
use App\Enums\SecurityDeviceStatus;
use App\Models\Kit;
use App\Models\KitType;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateKit
{
    public function __construct(private FindSkippedSecurityDevice $findSkippedDevice) {}

    /**
     * Create a kit in Armando, with a copy of what its kit type carries and its pouch already inside, all or nothing
     * (H14). The pouch is the device scanned in the header: of the pouch type of the kit type, of a lot of the shipping
     * line of the kit and Disponible (T76). Return the reason the pouch cannot be used, to show it on screen, or the kit.
     *
     * @param  array{pouchCode: string, kitTypeId: int, shippingLineId: int}  $data
     */
    public function handle(array $data, User $user): Kit|string
    {
        return DB::transaction(function () use ($data, $user): Kit|string {
            $kitType = KitType::query()->with('securityDeviceTypes')->findOrFail($data['kitTypeId']);
            $shippingLine = ShippingLine::query()->findOrFail($data['shippingLineId']);
            $pouchType = $kitType->securityDeviceTypes->first(fn (SecurityDeviceType $type): bool => $type->kind === SecurityDeviceKind::Pouch);

            if ($pouchType === null) {
                return sprintf('El tipo de kit %s no lleva funda. Edítalo en Tipos de kit y enciende una.', $kitType->name);
            }

            $pouch = $this->pouch($data['pouchCode'], $pouchType);

            if ($pouch === null) {
                return sprintf('No hay ninguna funda con el código %s.', $data['pouchCode']);
            }

            $reason = $this->rejectionReason($pouch, $pouchType, $shippingLine);

            if ($reason !== null) {
                return $reason;
            }

            $skipped = $this->findSkippedDevice->handle([$pouch->id]);

            if ($skipped !== null) {
                return FindSkippedSecurityDevice::message($skipped);
            }

            $kit = Kit::query()->create([
                'pouch_code' => $data['pouchCode'],
                'kit_type_id' => $kitType->id,
                'shipping_line_id' => $shippingLine->id,
                'status' => KitStatus::Building,
                'created_by' => $user->id,
            ]);

            $kit->securityDeviceTypes()->attach($kitType->securityDeviceTypes->mapWithKeys(function (SecurityDeviceType $securityDeviceType): array {
                /** @var object{quantity: int, internal_quantity: int|null, external_quantity: int|null} $pivot */
                $pivot = $securityDeviceType->getRelationValue('pivot');

                return [$securityDeviceType->id => [
                    'quantity' => $pivot->quantity,
                    'internal_quantity' => $pivot->internal_quantity,
                    'external_quantity' => $pivot->external_quantity,
                ]];
            })->all());

            $kit->devices()->attach($pouch->id, ['assignment' => null]);
            $pouch->update(['status' => SecurityDeviceStatus::InKit]);
            $kit->syncStatus();

            return $kit;
        });
    }

    /**
     * Find the device with the code, locked for the change: of the pouch type first, because two types that do not use
     * a series can share a serial number (T62), or else of any type, to say what it is.
     */
    private function pouch(string $code, SecurityDeviceType $pouchType): ?SecurityDevice
    {
        $query = fn () => SecurityDevice::query()
            ->with(['lot.securityDeviceType', 'lot.announcement.shippingLine'])
            ->where('serial_number', $code)
            ->lockForUpdate();

        return $query()->ofType($pouchType->id)->first() ?? $query()->first();
    }

    /**
     * Get why the device cannot be the pouch of the kit, or null when it can (T76).
     */
    private function rejectionReason(SecurityDevice $device, SecurityDeviceType $pouchType, ShippingLine $shippingLine): ?string
    {
        $announcement = $device->lot->announcement;
        $deviceType = $device->lot->securityDeviceType;

        if ($deviceType->kind !== SecurityDeviceKind::Pouch) {
            return sprintf('%s es un dispositivo %s, no una funda.', $device->serial_number, $deviceType->name);
        }

        if ($deviceType->id !== $pouchType->id) {
            return sprintf('La funda %s es del tipo %s y el tipo de kit lleva %s.', $device->serial_number, $deviceType->name, $pouchType->name);
        }

        if ($announcement->shipping_line_id !== $shippingLine->id) {
            return sprintf(
                'La funda %s es de %s · %s y el kit es de %s · %s.',
                $device->serial_number,
                $announcement->shippingLine->code,
                $announcement->shippingLine->name,
                $shippingLine->code,
                $shippingLine->name,
            );
        }

        if ($device->status === SecurityDeviceStatus::Inactive) {
            return sprintf('La funda %s está Inactiva: falta registrar la aprobación de la naviera.', $device->serial_number);
        }

        if ($device->status !== SecurityDeviceStatus::Available) {
            return sprintf('La funda %s está %s y solo se usa una Disponible.', $device->serial_number, $device->status->label());
        }

        return null;
    }
}
