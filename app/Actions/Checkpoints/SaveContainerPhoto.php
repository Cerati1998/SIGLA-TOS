<?php

namespace App\Actions\Checkpoints;

use App\Models\Checkpoint;
use App\Models\User;
use App\Services\PhotoStorage;
use Illuminate\Http\UploadedFile;
use Throwable;

/**
 * Save the optional photo of the container in its checkpoint, as evidence (H34, criterion 1). There is one per
 * checkpoint: a new one replaces the previous one, whose files are deleted (decision of the user of 2026-11-24). It
 * does not read the number in the photo.
 */
class SaveContainerPhoto
{
    public function __construct(private PhotoStorage $photoStorage) {}

    public function handle(Checkpoint $checkpoint, UploadedFile $photo, UploadedFile $thumbnail, User $photographer): Checkpoint
    {
        $previous = $checkpoint->storedContainerPhoto();
        $storedPhoto = $this->photoStorage->store($photo, $thumbnail, AddCheckpointPhoto::directory($checkpoint).'/container');

        try {
            $checkpoint->forceFill([
                'container_photo_path' => $storedPhoto->path,
                'container_thumbnail_path' => $storedPhoto->thumbnailPath,
                'container_photo_by' => $photographer->id,
                'container_photo_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            $this->photoStorage->delete($storedPhoto);

            throw $exception;
        }

        if ($previous !== null) {
            $this->photoStorage->delete($previous);
        }

        return $checkpoint;
    }
}
