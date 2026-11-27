<?php

namespace App\Actions\GpsDevices;

use App\Models\GpsDevice;
use App\Services\PhotoStorage;
use App\Services\StoredPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class SaveGpsDevice
{
    /**
     * The directory of the reference photos of the GPS devices on the private disk.
     */
    public const string PHOTO_DIRECTORY = 'gps-devices';

    public function __construct(private PhotoStorage $photoStorage) {}

    /**
     * Create a GPS device, or save the changes to the given one, with its reference photo (H40). A new photo replaces
     * the one it had, and removing the photo forgets it; the old files are deleted only once the device is saved. If
     * saving fails, the new photo is not kept.
     *
     * @param  array<string, mixed>  $attributes  The columns of the device, without the photo.
     */
    public function handle(?GpsDevice $gpsDevice, array $attributes, ?UploadedFile $photo, ?UploadedFile $thumbnail, bool $removePhoto): GpsDevice
    {
        $newPhoto = $photo !== null && $thumbnail !== null
            ? $this->photoStorage->store($photo, $thumbnail, self::PHOTO_DIRECTORY)
            : null;
        $oldPhoto = $gpsDevice?->photo_path !== null && $gpsDevice->thumbnail_path !== null && ($newPhoto !== null || $removePhoto)
            ? new StoredPhoto($gpsDevice->photo_path, $gpsDevice->thumbnail_path)
            : null;

        if ($newPhoto !== null) {
            $attributes += ['photo_path' => $newPhoto->path, 'thumbnail_path' => $newPhoto->thumbnailPath];
        } elseif ($removePhoto) {
            $attributes += ['photo_path' => null, 'thumbnail_path' => null];
        }

        try {
            $saved = DB::transaction(function () use ($gpsDevice, $attributes): GpsDevice {
                if ($gpsDevice === null) {
                    return GpsDevice::query()->create($attributes);
                }

                $gpsDevice->update($attributes);

                return $gpsDevice;
            });
        } catch (Throwable $exception) {
            if ($newPhoto !== null) {
                $this->photoStorage->delete($newPhoto);
            }

            throw $exception;
        }

        if ($oldPhoto !== null) {
            $this->photoStorage->delete($oldPhoto);
        }

        return $saved;
    }
}
