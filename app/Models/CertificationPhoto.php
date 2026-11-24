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
 * with its thumbnail. Both files are on the private disk. Who uploaded it and when is the evidence. When a device of
 * its section is replaced (H38), the photo stays as it was "before the replacement", but it no longer counts.
 *
 * @property int $id
 * @property int $certification_step_id
 * @property string $path
 * @property string $thumbnail_path
 * @property int $uploaded_by
 * @property int|null $superseded_by_replacement_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read CertificationStep $step
 * @property-read User $uploader
 * @property-read SecurityDeviceReplacement|null $supersedingReplacement
 */
#[Fillable(['certification_step_id', 'path', 'thumbnail_path', 'uploaded_by', 'superseded_by_replacement_id'])]
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
     * Get the replacement that left the photo out of date, if any (H38).
     *
     * @return BelongsTo<SecurityDeviceReplacement, $this>
     */
    public function supersedingReplacement(): BelongsTo
    {
        return $this->belongsTo(SecurityDeviceReplacement::class, 'superseded_by_replacement_id');
    }

    /**
     * Whether a replacement of a device of its section left the photo out of date: it is shown "before the replacement"
     * and no longer counts (H38, criterion 6).
     */
    public function isSuperseded(): bool
    {
        return $this->superseded_by_replacement_id !== null;
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
