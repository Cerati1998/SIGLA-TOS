<?php

namespace App\Actions\Certifications;

use App\Enums\KitStatus;
use App\Enums\SecurityDeviceStatus;
use App\Models\Certification;
use App\Models\Kit;
use App\Models\SecurityDevice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Close a certification whose two inspections are complete (H19). Its kit and the devices of the kit become Usado,
 * all or none. A reopened certification is closed again the same way; its kit is already Usado.
 */
class CloseCertification
{
    /**
     * Close the certification, checking it again under a lock in case another user closed it or deleted a photo.
     *
     * @throws ValidationException With the key "certification", when it is closed already or a section has no photo.
     */
    public function handle(Certification $certification, User $user): void
    {
        DB::transaction(function () use ($certification, $user): void {
            $locked = Certification::query()->whereKey($certification->id)->lockForUpdate()->firstOrFail();

            if ($locked->isClosed()) {
                throw ValidationException::withMessages(['certification' => 'Ya estaba cerrada.']);
            }

            if (! $locked->inspectionsAreComplete()) {
                throw ValidationException::withMessages(['certification' => 'Falta al menos una foto en una sección: la interna y la externa deben estar completas.']);
            }

            $locked->update(['closed_at' => now(), 'closed_by' => $user->id]);

            Kit::query()->whereKey($locked->kit_id)->update(['status' => KitStatus::Used]);
            SecurityDevice::query()
                ->whereIn('id', DB::table('kit_security_device')->where('kit_id', $locked->kit_id)->select('security_device_id'))
                ->update(['status' => SecurityDeviceStatus::Used]);
        });
    }
}
