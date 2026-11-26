<?php

namespace App\Actions\Reinspections;

use App\Models\Certification;
use App\Models\CheckpointMark;
use App\Models\Reinspection;
use App\Models\ReinspectionDevice;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Close the reinspection of a certification (H35, criteria 7 and 8): every reopened section has its new device and at
 * least one new photo. It keeps who closed it and when, and the checkpoint gets one unmarked mark for each new series
 * in the steps that verified the replaced one, whose marks no longer count. The unit goes back to the checkpoint. All
 * or nothing.
 */
class CloseReinspection
{
    /**
     * @throws ValidationException With the key "reinspection", when it cannot be closed.
     */
    public function handle(Certification $certification, User $user): Reinspection
    {
        return DB::transaction(function () use ($certification, $user): Reinspection {
            $certification = Certification::query()->lockForUpdate()->findOrFail($certification->id);
            $reinspection = $certification->openReinspection()->lockForUpdate()->with('devices.replacement')->first();

            if ($reinspection === null) {
                throw ValidationException::withMessages(['reinspection' => 'La certificación no está en reinspección.']);
            }

            if (! $reinspection->isComplete()) {
                throw ValidationException::withMessages(['reinspection' => 'Falta reemplazar dispositivos o tomar fotos nuevas en las secciones reabiertas.']);
            }

            $reinspection->forceFill(['closed_at' => now(), 'closed_by' => $user->id])->save();

            $reinspection->devices->each(fn (ReinspectionDevice $device) => $this->verifyAgain($reinspection, $device));

            return $reinspection;
        });
    }

    /**
     * Give the new series its mark in each step of the checkpoint that verified the replaced one, and leave the marks
     * of the replaced one out (criterion 8).
     */
    private function verifyAgain(Reinspection $reinspection, ReinspectionDevice $device): void
    {
        /** @var int $installedId */
        $installedId = $device->replacement?->installed_device_id;

        CheckpointMark::query()
            ->whereNull('superseded_by_reinspection_id')
            ->where('security_device_id', $device->security_device_id)
            ->whereHas('step', fn (Builder $steps) => $steps->where('checkpoint_id', $reinspection->checkpoint_id))
            ->get()
            ->each(function (CheckpointMark $mark) use ($reinspection, $installedId): void {
                CheckpointMark::query()->create([
                    'checkpoint_step_id' => $mark->checkpoint_step_id,
                    'security_device_id' => $installedId,
                    'reinspection_id' => $reinspection->id,
                ]);

                $mark->forceFill(['superseded_by_reinspection_id' => $reinspection->id])->save();
            });
    }
}
