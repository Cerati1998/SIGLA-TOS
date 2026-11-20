<?php

namespace App\Models;

use App\Services\StoredPhoto;
use Database\Factories\CertificationPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A photo of a section of a certification (H18), taken with the camera of the phone and compressed in the browser,
 * with its thumbnail. Both files are on the private disk. Who uploaded it and when is the evidence.
 *
 * @property int $id
 * @property int $certification_step_id
 * @property string $path
 * @property string $thumbnail_path
 * @property int $uploaded_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read CertificationStep $step
 * @property-read User $uploader
 */
#[Fillable(['certification_step_id', 'path', 'thumbnail_path', 'uploaded_by'])]
class CertificationPhoto extends Model
{
    /** @use HasFactory<CertificationPhotoFactory> */
    use HasFactory;

    /**
     * Get the section of the certification the photo belongs to.
     *
     * @return BelongsTo<CertificationStep, $this>
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(CertificationStep::class, 'certification_step_id');
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
