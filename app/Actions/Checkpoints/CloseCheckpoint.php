<?php

namespace App\Actions\Checkpoints;

use App\Models\Checkpoint;
use App\Models\CheckpointMark;
use App\Models\CheckpointStep;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Close a checkpoint (H34, criteria 8 and 9): every photo step has a photo, every series is marked and all of them are
 * correct. It keeps the observations and who closed it and when. With an incorrect series it is not closed: it starts a
 * reinspection instead (H35), and while it waits for one it is not closed either.
 */
class CloseCheckpoint
{
    public function handle(Checkpoint $checkpoint, ?string $observations, User $closer): Checkpoint
    {
        return DB::transaction(function () use ($checkpoint, $observations, $closer): Checkpoint {
            $checkpoint = Checkpoint::query()->lockForUpdate()->with(['steps.photos', 'steps.marks'])->findOrFail($checkpoint->id);

            if ($checkpoint->isClosed()) {
                throw ValidationException::withMessages(['checkpoint' => 'El checkpoint ya estaba cerrado.']);
            }

            if ($checkpoint->isInReinspection()) {
                throw ValidationException::withMessages(['checkpoint' => 'El checkpoint está en reinspección: se cierra cuando vuelva.']);
            }

            if (! self::isComplete($checkpoint)) {
                throw ValidationException::withMessages(['checkpoint' => 'Falta completar pasos: cada paso de fotos necesita una foto y cada serie, su marca.']);
            }

            if (self::hasIncorrectSeries($checkpoint)) {
                throw ValidationException::withMessages(['checkpoint' => 'Hay series incorrectas: inicia la reinspección.']);
            }

            $checkpoint->forceFill([
                'observations' => self::clean($observations),
                'closed_at' => now(),
                'closed_by' => $closer->id,
            ])->save();

            return $checkpoint;
        });
    }

    /**
     * Whether every step is complete, reading the photos and marks already loaded.
     */
    public static function isComplete(Checkpoint $checkpoint): bool
    {
        return $checkpoint->steps->every(fn (CheckpointStep $step): bool => $step->isComplete());
    }

    /**
     * Whether any series was marked incorrect, reading the marks already loaded.
     */
    public static function hasIncorrectSeries(Checkpoint $checkpoint): bool
    {
        return $checkpoint->steps->contains(
            fn (CheckpointStep $step): bool => $step->marks->contains(fn (CheckpointMark $mark): bool => $mark->is_correct === false)
        );
    }

    /**
     * Get the observations without the spaces around them, or nothing if they are empty.
     */
    public static function clean(?string $observations): ?string
    {
        $observations = trim((string) $observations);

        return $observations === '' ? null : $observations;
    }
}
