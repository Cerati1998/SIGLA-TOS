<?php

namespace App\Http\Controllers;

use App\Models\SecurityDevice;
use App\Services\PhotoStorage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves the photo of the discard of a device from the private disk, only with a session (T77).
 */
class SecurityDeviceDiscardPhotoController extends Controller
{
    /**
     * Show the photo at its full size.
     */
    public function show(SecurityDevice $securityDevice, PhotoStorage $photoStorage): StreamedResponse
    {
        abort_if($securityDevice->discard_photo_path === null, 404);

        return $photoStorage->response($securityDevice->discard_photo_path);
    }

    /**
     * Show the thumbnail of the photo.
     */
    public function thumbnail(SecurityDevice $securityDevice, PhotoStorage $photoStorage): StreamedResponse
    {
        abort_if($securityDevice->discard_thumbnail_path === null, 404);

        return $photoStorage->response($securityDevice->discard_thumbnail_path);
    }
}
