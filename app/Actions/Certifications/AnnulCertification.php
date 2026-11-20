<?php

namespace App\Actions\Certifications;

use App\Enums\CertificationKitDecision;
use App\Enums\KitStatus;
use App\Enums\SecurityDeviceStatus;
use App\Models\Certification;
use App\Models\Kit;
use App\Models\SecurityDevice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Annul the assignment of the kit of a certification that is not closed, when the dispatch service is cancelled (H32).
 * The certification is kept, annulled. Its kit is reused, back to Despachado with its devices out of the sections, or
 * disabled, with all its devices discarded, as the shipping line answers. All or nothing.
 */
class AnnulCertification
{
    /**
     * Annul the certification and keep who annulled it, when, why and what was done with the kit.
     *
     * @throws ValidationException With the key "certification", when it is closed or annulled already.
     */
    public function handle(Certification $certification, string $reason, CertificationKitDecision $decision, User $user): void
    {
        DB::transaction(function () use ($certification, $reason, $decision, $user): void {
            $locked = Certification::query()->whereKey($certification->id)->lockForUpdate()->firstOrFail();

            if ($locked->isAnnulled()) {
                throw ValidationException::withMessages(['certification' => 'Su asignación ya estaba anulada.']);
            }

            if ($locked->isClosed()) {
                throw ValidationException::withMessages(['certification' => 'Está cerrada: una certificación cerrada no se anula.']);
            }

            $locked->update([
                'annulled_at' => now(),
                'annulled_by' => $user->id,
                'annulment_reason' => $reason,
                'kit_decision' => $decision,
            ]);

            $kitDevices = DB::table('kit_security_device')->where('kit_id', $locked->kit_id)->select('security_device_id');

            if ($decision === CertificationKitDecision::Reuse) {
                DB::table('certification_step_security_device')
                    ->whereIn('certification_step_id', $locked->steps()->select('id'))
                    ->delete();
                Kit::query()->whereKey($locked->kit_id)->update(['status' => KitStatus::Dispatched]);

                return;
            }

            Kit::query()->whereKey($locked->kit_id)->update(['status' => KitStatus::Disabled]);
            SecurityDevice::query()->whereIn('id', $kitDevices)->update([
                'status' => SecurityDeviceStatus::Discarded,
                'discarded_at' => now(),
                'discarded_by' => $user->id,
                'discard_reason' => mb_substr('Asignación anulada: '.$reason, 0, 255),
            ]);
        });
    }
}
