<?php

namespace App\Actions\Certifications;

use App\Models\CertificationPhoto;
use App\Models\CertificationStep;
use App\Models\User;
use App\Services\PhotoStorage;
use Illuminate\Http\UploadedFile;
use Throwable;

/**
 * Add a photo, already compressed in the browser, to a section of a certification (H18, criteria 5 and 8). Who
 * uploaded it and when stay with it.
 */
class AddCertificationPhoto
{
    public function __construct(private PhotoStorage $photoStorage) {}

    public function handle(CertificationStep $step, UploadedFile $photo, UploadedFile $thumbnail, User $uploader): CertificationPhoto
    {
        $storedPhoto = $this->photoStorage->store($photo, $thumbnail, self::directory($step));

        try {
            return $step->photos()->create([
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
     * Get the directory of the photos of a certification on the private disk.
     */
    public static function directory(CertificationStep $step): string
    {
        return 'certifications/'.$step->certification_id;
    }
}
