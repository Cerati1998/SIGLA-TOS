<?php

namespace App\Actions\Kits;

use App\Enums\SecurityDeviceStatus;
use App\Models\Kit;
use Illuminate\Support\Facades\DB;

class RemoveSecurityDeviceFromKit
{
    /**
     * Take a device out of the kit: it becomes available again and the kit goes back to Armando (H14, criteria 9 and
     * 14). The pouch stays: it is the header of the kit (T76). Return the reason it was not removed, to show it on
     * screen, or null when it was removed.
     */
    public function handle(Kit $kit, int $securityDeviceId): ?string
    {
        return DB::transaction(function () use ($kit, $securityDeviceId): ?string {
            $kit = Kit::query()->lockForUpdate()->findOrFail($kit->id);

            if (! $kit->status->contentsCanChange()) {
                return 'El kit ya se despachó: su contenido no cambia.';
            }

            $device = $kit->devices()->whereKey($securityDeviceId)->lockForUpdate()->first();

            if ($device === null) {
                return 'Ese dispositivo ya no está en el kit.';
            }

            if ($device->lot->announcement->security_device_type_id === $kit->pouchType()?->id) {
                return 'La funda no se quita: es la cabecera del kit.';
            }

            $kit->devices()->detach($device->id);
            $device->update(['status' => SecurityDeviceStatus::Available]);
            $kit->syncStatus();

            return null;
        });
    }
}
