<?php

namespace App\Actions\Reinspections;

use App\Actions\Checkpoints\CloseCheckpoint;
use App\Models\Certification;
use App\Models\CertificationStep;
use App\Models\Checkpoint;
use App\Models\CheckpointMark;
use App\Models\CheckpointStep;
use App\Models\Reinspection;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Start a reinspection from a checkpoint with incorrect series (H35, criteria 1 and 2): with every step complete, it
 * keeps the reason and who started it, and one device to replace for each incorrect series, with the section of the
 * certification where it is placed, which is reopened. The checkpoint is not closed and waits for it. All or nothing.
 */
class StartReinspection
{
    /**
     * @throws ValidationException With the key "reinspection", when it cannot be started.
     */
    public function handle(Checkpoint $checkpoint, string $reason, User $user): Reinspection
    {
        return DB::transaction(function () use ($checkpoint, $reason, $user): Reinspection {
            $checkpoint = Checkpoint::query()->lockForUpdate()->with(['steps.photos', 'steps.marks.device'])->findOrFail($checkpoint->id);
            $certification = Certification::query()->lockForUpdate()->findOrFail($checkpoint->certification_id);

            $this->ensureItCanStart($checkpoint, $certification);

            $reinspection = $checkpoint->reinspections()->create([
                'certification_id' => $certification->id,
                'reason' => $reason,
                'started_by' => $user->id,
            ]);

            $this->incorrectMarks($checkpoint)
                ->unique('security_device_id')
                ->each(fn (CheckpointMark $mark) => $reinspection->devices()->create([
                    'certification_step_id' => $this->stepOf($certification, $mark)->id,
                    'security_device_id' => $mark->security_device_id,
                ]));

            return $reinspection;
        });
    }

    /**
     * Refuse to start it when the checkpoint is closed or already in reinspection, when a step is still incomplete or
     * when no series is incorrect.
     *
     * @throws ValidationException
     */
    private function ensureItCanStart(Checkpoint $checkpoint, Certification $certification): void
    {
        $refusal = match (true) {
            $checkpoint->isClosed() => 'El checkpoint ya estaba cerrado.',
            $checkpoint->isInReinspection() => 'El checkpoint ya está en reinspección.',
            $certification->isAnnulled() => 'Se anuló la asignación de su kit: no admite reinspección.',
            ! CloseCheckpoint::isComplete($checkpoint) => 'Falta completar pasos: cada paso de fotos necesita una foto y cada serie, su marca.',
            $this->incorrectMarks($checkpoint)->isEmpty() => 'No hay series incorrectas: el checkpoint se puede cerrar.',
            default => null,
        };

        if ($refusal !== null) {
            throw ValidationException::withMessages(['reinspection' => $refusal]);
        }
    }

    /**
     * Get the marks that count and are incorrect, reading the marks already loaded.
     *
     * @return Collection<int, CheckpointMark>
     */
    private function incorrectMarks(Checkpoint $checkpoint): Collection
    {
        return $checkpoint->steps
            ->flatMap(fn (CheckpointStep $step) => $step->marks)
            ->filter(fn (CheckpointMark $mark): bool => $mark->is_correct === false)
            ->values()
            ->toBase();
    }

    /**
     * Get the section of the certification where the device of an incorrect series is placed.
     *
     * @throws ValidationException When it is no longer placed in a section.
     */
    private function stepOf(Certification $certification, CheckpointMark $mark): CertificationStep
    {
        $step = $certification->steps()
            ->whereHas('devices', fn ($devices) => $devices->whereKey($mark->security_device_id))
            ->first();

        if ($step === null) {
            throw ValidationException::withMessages(['reinspection' => sprintf('La serie %s ya no está en una sección de la certificación.', $mark->device->serial_number)]);
        }

        return $step;
    }
}
