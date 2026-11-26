<?php

namespace App\Actions\Reinspections;

use App\Actions\Certifications\ReplaceSecurityDevice;
use App\Models\ReinspectionDevice;
use App\Models\SecurityDevice;
use App\Models\User;
use Illuminate\Http\UploadedFile;

/**
 * Replace a device to replace of a reinspection by the extra device whose series was scanned or typed (H35, criterion
 * 5), with the replacement of H38: the extra takes its place in the section and the one that left becomes Descartado,
 * with the reason of the reinspection and the photo of the discard.
 */
class ReplaceReinspectionDevice
{
    public function __construct(private ReplaceSecurityDevice $replaceDevice) {}

    /**
     * Replace the device and return null, or return why it was not replaced, to show it on screen.
     */
    public function handle(ReinspectionDevice $reinspectionDevice, string $serialNumber, UploadedFile $photo, UploadedFile $thumbnail, User $user): ?string
    {
        $reinspectionDevice->loadMissing(['reinspection.certification', 'step', 'device']);
        $serialNumber = mb_strtoupper(trim($serialNumber));
        $installed = $this->extraDevice($reinspectionDevice, $serialNumber);

        if ($installed === null) {
            return sprintf('%s no es un dispositivo extra de esta unidad: bodega debe despacharlo a granel, por Operación, para este contenedor.', $serialNumber);
        }

        return $this->replaceDevice->handle(
            $reinspectionDevice->step,
            $reinspectionDevice->device,
            $installed,
            $reinspectionDevice->reinspection->reason,
            $photo,
            $thumbnail,
            $user,
            $reinspectionDevice,
        );
    }

    /**
     * Get the extra device of the unit with that series, if it is one.
     */
    private function extraDevice(ReinspectionDevice $reinspectionDevice, string $serialNumber): ?SecurityDevice
    {
        return $reinspectionDevice->reinspection->certification->extraDevices()
            ->where('serial_number', $serialNumber)
            ->first();
    }
}
