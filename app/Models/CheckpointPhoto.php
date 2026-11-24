<?php

namespace App\Models;

use App\Services\StoredPhoto;
use Database\Factories\CheckpointPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A photo of a checkpoint (H34): of one of its photo steps, or an extra one of the last step, which belongs to no step.
 * It is taken with the camera of the phone and compressed in the browser, like the photos of a certification (H18),
 * with its thumbnail on the private disk. Who uploaded it and when is the evidence.
 *
 * @property int $id
 * @property int $checkpoint_id
 * @property int|null $checkpoint_step_id
 * @property string $path
 * @property string $thumbnail_path
 * @property int $uploaded_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Checkpoint $checkpoint
 * @property-read CheckpointStep|null $step
 * @property-read User $uploader
 */
#[Fillable(['checkpoint_id', 'checkpoint_step_id', 'path', 'thumbnail_path', 'uploaded_by'])]
class CheckpointPhoto extends Model
{
    /** @use HasFactory<CheckpointPhotoFactory> */
    use HasFactory;

    /**
     * Get the checkpoint the photo belongs to.
     *
     * @return BelongsTo<Checkpoint, $this>
     */
    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(Checkpoint::class);
    }

    /**
     * Get the step of the photo; an extra photo has none.
     *
     * @return BelongsTo<CheckpointStep, $this>
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(CheckpointStep::class, 'checkpoint_step_id');
    }

    /**
     * Get the user who uploaded the photo, even if they were deactivated afterwards.
     *
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by')->withTrashed();
    }

    /**
     * Get the files of the photo, to replace or delete them.
     */
    public function storedPhoto(): StoredPhoto
    {
        return new StoredPhoto($this->path, $this->thumbnail_path);
    }
}
