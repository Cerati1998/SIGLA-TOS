<?php

namespace App\Actions\Certifications;

use App\Models\Certification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reopen a closed certification with a reason, so its photos can be corrected or added and it can be closed again
 * (H19, criterion 7). Its kit and its devices stay Usado. One in reinspection is not reopened (H35, criterion 9).
 */
class ReopenCertification
{
    /**
     * Reopen the certification and keep who reopened it, when and why, with who had closed it and when.
     *
     * @throws ValidationException With the key "certification", when it is not closed.
     */
    public function handle(Certification $certification, string $reason, User $user): void
    {
        DB::transaction(function () use ($certification, $reason, $user): void {
            $locked = Certification::query()->whereKey($certification->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isClosed()) {
                throw ValidationException::withMessages(['certification' => 'No está cerrada: ya se puede corregir.']);
            }

            if ($locked->isInReinspection()) {
                throw ValidationException::withMessages(['certification' => 'Está en reinspección: se reapertura cuando se cierre la reinspección.']);
            }

            $locked->reopenings()->create([
                'reason' => $reason,
                'reopened_by' => $user->id,
                'closed_at' => $locked->closed_at,
                'closed_by' => $locked->closed_by,
            ]);
            $locked->update(['closed_at' => null, 'closed_by' => null]);
        });
    }
}
