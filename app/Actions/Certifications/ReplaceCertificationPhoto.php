<?php

namespace App\Actions\Certifications;

use App\Models\CertificationPhoto;
use App\Models\User;
use App\Services\PhotoStorage;
use Illuminate\Http\UploadedFile;
use Throwable;

/**
 * Replace a photo of a section with a new one taken with the camera (H18, criterion 8). The new photo takes the place
 * of the old one, with who uploaded it and when; the files of the old one are deleted.
 */
class ReplaceCertificationPhoto
{
    public function __construct(private PhotoStorage $photoStorage) {}

    public function handle(CertificationPhoto $certificationPhoto, UploadedFile $photo, UploadedFile $thumbnail, User $uploader): CertificationPhoto
    {
        $previous = $certificationPhoto->storedPhoto();
        $storedPhoto = $this->photoStorage->store($photo, $thumbnail, AddCertificationPhoto::directory($certificationPhoto->step));

        try {
            $certificationPhoto->forceFill([
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

        return $certificationPhoto;
    }
}
