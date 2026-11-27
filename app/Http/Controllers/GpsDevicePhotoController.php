<?php

namespace App\Http\Controllers;

use App\Models\GpsDevice;
use App\Services\PhotoStorage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves the reference photo of a GPS device from the private disk, only with a session (H40).
 */
class GpsDevicePhotoController extends Controller
{
    /**
     * Show the photo at its full size.
     */
    public function show(GpsDevice $gpsDevice, PhotoStorage $photoStorage): StreamedResponse
    {
        abort_if($gpsDevice->photo_path === null, 404);

        return $photoStorage->response($gpsDevice->photo_path);
    }

    /**
     * Show the thumbnail of the photo.
     */
    public function thumbnail(GpsDevice $gpsDevice, PhotoStorage $photoStorage): StreamedResponse
    {
        abort_if($gpsDevice->thumbnail_path === null, 404);

        return $photoStorage->response($gpsDevice->thumbnail_path);
    }
}
