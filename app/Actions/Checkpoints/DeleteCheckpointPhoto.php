<?php

namespace App\Actions\Checkpoints;

use App\Models\CheckpointPhoto;
use App\Services\PhotoStorage;

/**
 * Delete a photo of a checkpoint and its files, once the inspector confirmed it (H34, with the rules of H18).
 */
class DeleteCheckpointPhoto
{
    public function __construct(private PhotoStorage $photoStorage) {}

    public function handle(CheckpointPhoto $checkpointPhoto): void
    {
        $storedPhoto = $checkpointPhoto->storedPhoto();

        $checkpointPhoto->delete();

        $this->photoStorage->delete($storedPhoto);
    }
}
