<?php

namespace App\Actions\Certifications;

use App\Enums\CertificationSectionType;
use App\Enums\SecurityDeviceStatus;
use App\Models\BulkDispatch;
use App\Models\Certification;
use App\Models\CertificationStep;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceReplacement;

/**
 * List the extra devices of a certification, unused or already used, and the devices of the kit placed in the sections
 * of one of its inspections, which an unused extra of the same type can replace (H38, criteria 3 and 4). The screen
 * filters both lists by type in the browser.
 *
 * @phpstan-type Extra array{id: int, serialNumber: string, typeId: int, typeName: string, dispatch: string, dispatchedTo: string, dispatchedAt: string, unused: bool, replaced: array{serialNumber: string, section: string, by: string, at: string}|null}
 * @phpstan-type Placed array{id: int, serialNumber: string, typeId: int, typeName: string, stepId: int, section: string}
 */
class ListExtraDevices
{
    /**
     * @return array{extras: list<Extra>, placed: list<Placed>}
     */
    public function handle(Certification $certification, CertificationSectionType $type): array
    {
        $replacements = $certification->replacements()
            ->with(['removedDevice', 'step.section', 'replacer'])
            ->get()
            ->keyBy('installed_device_id');

        $extras = $certification->extraDevices()
            ->with(['lot.securityDeviceType', 'bulkDispatches.receiver'])
            ->orderBy('serial_number')
            ->get()
            ->map(fn (SecurityDevice $device): array => $this->extra($device, $replacements->get($device->id)))
            ->sortByDesc('unused')
            ->all();

        $placed = $certification->steps()
            ->where('type', $type)
            ->with('section', 'devices.lot.securityDeviceType')
            ->get()
            ->flatMap(fn (CertificationStep $step) => $step->devices->map(fn (SecurityDevice $device): array => [
                'id' => $device->id,
                'serialNumber' => $device->serial_number,
                'typeId' => $device->lot->security_device_type_id,
                'typeName' => $device->lot->securityDeviceType->name,
                'stepId' => $step->id,
                'section' => $step->section->name,
            ]))
            ->all();

        return ['extras' => array_values($extras), 'placed' => array_values($placed)];
    }

    /**
     * Describe an extra device: its dispatch and, once used, the device it replaced.
     *
     * @return Extra
     */
    private function extra(SecurityDevice $device, ?SecurityDeviceReplacement $replacement): array
    {
        /** @var BulkDispatch $dispatch */
        $dispatch = $device->bulkDispatches->first();
        $dispatchedAt = ($dispatch->created_at ?? now())->timezone('America/Lima');

        return [
            'id' => $device->id,
            'serialNumber' => $device->serial_number,
            'typeId' => $device->lot->security_device_type_id,
            'typeName' => $device->lot->securityDeviceType->name,
            'dispatch' => $dispatch->number(),
            'dispatchedTo' => $dispatch->receiver->full_name,
            'dispatchedAt' => $dispatchedAt->format('d/m/Y').' a las '.$dispatchedAt->format('H:i'),
            'unused' => $replacement === null && $device->status === SecurityDeviceStatus::Dispatched,
            'replaced' => $replacement === null ? null : $this->replaced($replacement),
        ];
    }

    /**
     * Describe the device an extra device replaced, where, who and when.
     *
     * @return array{serialNumber: string, section: string, by: string, at: string}
     */
    private function replaced(SecurityDeviceReplacement $replacement): array
    {
        $replacedAt = ($replacement->created_at ?? now())->timezone('America/Lima');

        return [
            'serialNumber' => $replacement->removedDevice->serial_number,
            'section' => $replacement->step->section->name,
            'by' => $replacement->replacer->name,
            'at' => $replacedAt->format('d/m/Y').' a las '.$replacedAt->format('H:i'),
        ];
    }
}
