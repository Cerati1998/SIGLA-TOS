<?php

namespace App\Actions\Checkpoints;

use App\Models\Checkpoint;
use App\Models\CheckpointPhoto;
use App\Models\CheckpointStep;
use App\Models\User;
use App\Services\PhotoStorage;
use Illuminate\Http\UploadedFile;
use Throwable;

/**
 * Save a photo of a checkpoint (H34): of a photo step, or an extra one of the last step, without a step (criteria 5
 * and 7). The photo and its thumbnail arrive already compressed in the browser; if the record cannot be saved, the
 * stored files are deleted.
 */
class AddCheckpointPhoto
{
    public function __construct(private PhotoStorage $photoStorage) {}

    public function handle(Checkpoint $checkpoint, ?CheckpointStep $step, UploadedFile $photo, UploadedFile $thumbnail, User $uploader): CheckpointPhoto
    {
        $storedPhoto = $this->photoStorage->store($photo, $thumbnail, self::directory($checkpoint));

        try {
            return $checkpoint->photos()->create([
                'checkpoint_step_id' => $step?->id,
                'path' => $storedPhoto->path,
                'thumbnail_path' => $storedPhoto->thumbnailPath,
                'uploaded_by' => $uploader->id,
            ]);
        } catch (Throwable $exception) {
            $this->photoStorage->delete($storedPhoto);

            throw $exception;
        }
    }

    /**
     * Get the directory of the photos of a checkpoint on the private disk.
     */
    public static function directory(Checkpoint $checkpoint): string
    {
        return 'checkpoints/'.$checkpoint->id;
    }
}
