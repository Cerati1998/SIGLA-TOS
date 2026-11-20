<?php

namespace App\Actions\Certifications;

use App\Models\CertificationPhoto;
use App\Services\PhotoStorage;

/**
 * Delete a photo of a section and its files (H18, criterion 8).
 */
class DeleteCertificationPhoto
{
    public function __construct(private PhotoStorage $photoStorage) {}

    public function handle(CertificationPhoto $certificationPhoto): void
    {
        $storedPhoto = $certificationPhoto->storedPhoto();

        $certificationPhoto->delete();

        $this->photoStorage->delete($storedPhoto);
    }
}
