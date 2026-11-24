<?php

namespace App\Actions\SecurityDevices;

use App\Enums\SecurityDeviceStatus;
use App\Models\SecurityDevice;
use App\Models\User;
use App\Services\PhotoStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class DiscardSecurityDevice
{
    /**
     * The directory of the photos of the discards on the private disk.
     */
    public const string PHOTO_DIRECTORY = 'security-devices/discards';

    public function __construct(private PhotoStorage $photoStorage) {}

    /**
     * Discard a device of a lot, for example because it arrived broken (T65): it becomes Discarded, with who discarded
     * it, when, why and its photo, already compressed in the browser (T77), and can no longer go in a kit nor in a bulk
     * dispatch. Only an Inactive or Available device out of a kit and out of a bulk dispatch is discarded. Return why it
     * was not discarded, to show it on screen, or null. A photo of a device that was not discarded is not kept.
     */
    public function handle(SecurityDevice $device, string $reason, UploadedFile $photo, UploadedFile $thumbnail, User $user): ?string
    {
        $storedPhoto = $this->photoStorage->store($photo, $thumbnail, self::PHOTO_DIRECTORY);

        try {
            $refusal = DB::transaction(function () use ($device, $reason, $storedPhoto, $user): ?string {
                $locked = SecurityDevice::query()->with(['kits', 'bulkDispatches'])->lockForUpdate()->findOrFail($device->id);

                if ($locked->status === SecurityDeviceStatus::Discarded) {
                    return 'Ya estaba descartado.';
                }

                if ($locked->kits->isNotEmpty() || $locked->bulkDispatches->isNotEmpty() || ! $locked->status->canBeDiscarded()) {
                    return sprintf('Está %s: solo se descarta un dispositivo Inactivo o Disponible, fuera de un kit.', mb_strtolower($locked->status->label()));
                }

                $locked->update([
                    'status' => SecurityDeviceStatus::Discarded,
                    'discarded_at' => now(),
                    'discarded_by' => $user->id,
                    'discard_reason' => $reason,
                    'discard_photo_path' => $storedPhoto->path,
                    'discard_thumbnail_path' => $storedPhoto->thumbnailPath,
                ]);

                return null;
            });
        } catch (Throwable $exception) {
            $this->photoStorage->delete($storedPhoto);

            throw $exception;
        }

        if ($refusal !== null) {
            $this->photoStorage->delete($storedPhoto);
        }

        return $refusal;
    }
}
