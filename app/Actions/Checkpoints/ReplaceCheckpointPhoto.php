<?php

namespace App\Actions\Checkpoints;

use App\Models\CheckpointPhoto;
use App\Models\User;
use App\Services\PhotoStorage;
use Illuminate\Http\UploadedFile;
use Throwable;

/**
 * Put a new photo in the place of a photo of a checkpoint, with the same rules as a certification (H18): the new one
 * keeps who took it and when, and the files of the old one are deleted once the new one is saved.
 */
class ReplaceCheckpointPhoto
{
    public function __construct(private PhotoStorage $photoStorage) {}

    public function handle(CheckpointPhoto $checkpointPhoto, UploadedFile $photo, UploadedFile $thumbnail, User $uploader): CheckpointPhoto
    {
        $previous = $checkpointPhoto->storedPhoto();
        $storedPhoto = $this->photoStorage->store($photo, $thumbnail, AddCheckpointPhoto::directory($checkpointPhoto->checkpoint));

        try {
            $checkpointPhoto->forceFill([
                'path' => $storedPhoto->path,
                'thumbnail_path' => $storedPhoto->thumbnailPath,
                'uploaded_by' => $uploader->id,
                'created_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            $this->photoStorage->delete($storedPhoto);

            throw $exception;
        }

        $this->photoStorage->delete($previous);

        return $checkpointPhoto;
    }
}
