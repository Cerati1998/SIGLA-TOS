<?php

namespace App\Http\Controllers;

use App\Models\CertificationPhoto;
use App\Services\PhotoStorage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves the photos of the certifications from the private disk, only with a session (H18).
 */
class CertificationPhotoController extends Controller
{
    /**
     * Show a photo at its full size.
     */
    public function show(CertificationPhoto $certificationPhoto, PhotoStorage $photoStorage): StreamedResponse
    {
        return $photoStorage->response($certificationPhoto->path);
    }

    /**
     * Show the thumbnail of a photo.
     */
    public function thumbnail(CertificationPhoto $certificationPhoto, PhotoStorage $photoStorage): StreamedResponse
    {
        return $photoStorage->response($certificationPhoto->thumbnail_path);
    }
}
