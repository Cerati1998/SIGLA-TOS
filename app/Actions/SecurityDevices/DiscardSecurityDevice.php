<?php

namespace App\Actions\SecurityDevices;

use App\Enums\SecurityDeviceStatus;
use App\Models\SecurityDevice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DiscardSecurityDevice
{
    /**
     * Discard a device of a lot, for example because it arrived broken (T65): it becomes Discarded, with who discarded
     * it, when and why, and can no longer go in a kit nor in a bulk dispatch. Only an Inactive or Available device out of
     * a kit and out of a bulk dispatch is discarded. Return why it was not discarded, to show it on screen, or null.
     */
    public function handle(SecurityDevice $device, string $reason, User $user): ?string
    {
        return DB::transaction(function () use ($device, $reason, $user): ?string {
            $locked = SecurityDevice::query()->with(['kits', 'bulkDispatches'])->lockForUpdate()->findOrFail($device->id);

            if ($locked->status === SecurityDeviceStatus::Discarded) {
                return 'Ya estaba descartado.';
            }

            if ($locked->kits->isNotEmpty() || $locked->bulkDispatches->isNotEmpty() || ! $locked->status->canBeDiscarded()) {
                return sprintf('Está %s: solo se descarta un dispositivo Inactivo o Disponible, fuera de un kit.', mb_strtolower($locked->status->label()));
            }

            $locked->update([
                'status' => SecurityDeviceStatus::Discarded,
                'discarded_at' => now(),
                'discarded_by' => $user->id,
                'discard_reason' => $reason,
            ]);

            return null;
        });
    }
}
