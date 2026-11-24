<?php

namespace App\Actions\Checkpoints;

use App\Models\CheckpointMark;
use App\Models\User;

/**
 * Mark a series of a verification step as correct or incorrect (H34, criterion 6). The mark is saved when it is
 * touched and can be changed while the checkpoint is open; it keeps who marked it last and when (criterion 10).
 */
class MarkCheckpointSeries
{
    public function handle(CheckpointMark $mark, bool $isCorrect, User $marker): CheckpointMark
    {
        $mark->forceFill([
            'is_correct' => $isCorrect,
            'marked_by' => $marker->id,
            'marked_at' => now(),
        ])->save();

        return $mark;
    }
}
