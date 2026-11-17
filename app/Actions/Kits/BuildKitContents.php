<?php

namespace App\Actions\Kits;

use App\Enums\KitAssignment;
use App\Models\Kit;
use App\Models\SecurityDevice;

class BuildKitContents
{
    /**
     * Get the contents of a kit that its PDF and the PDF of its dispatch show (H15, criterion 14): the kit, what it
     * carries by type and its devices with their serial number, type and side.
     *
     * @return array{kit: Kit, slots: list<array{key: string, securityDeviceTypeId: int, name: string, assignment: KitAssignment|null, quantity: int, added: int}>, devices: list<array{serialNumber: string, type: string, assignment: string}>}
     */
    public function handle(Kit $kit): array
    {
        $kit->loadMissing(['kitType', 'shippingLine', 'devices.lot.announcement.securityDeviceType']);

        return [
            'kit' => $kit,
            'slots' => $kit->slots(),
            'devices' => array_values($kit->devices->map(function (SecurityDevice $device): array {
                /** @var object{assignment: string|null} $pivot */
                $pivot = $device->getRelationValue('pivot');

                return [
                    'serialNumber' => $device->serial_number,
                    'type' => $device->lot->announcement->securityDeviceType->name,
                    'assignment' => KitAssignment::tryFrom((string) $pivot->assignment)?->label() ?? '-',
                ];
            })->all()),
        ];
    }
}
