<?php

namespace App\Actions\Certifications;

use App\Actions\SecurityDevices\DiscardSecurityDevice;
use App\Enums\SecurityDeviceStatus;
use App\Models\BulkDispatch;
use App\Models\Certification;
use App\Models\CertificationStep;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceReplacement;
use App\Models\User;
use App\Services\PhotoStorage;
use App\Services\StoredPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Replace a device of the kit, already placed in a section of a certification, by one of its extra devices of the same
 * type (H38, criteria 4 to 7 and 10). The extra takes its place in the section and in the kit, with its status; the one
 * that left becomes Descartado, with the reason and its photo, as a discard (T77); and the photos the section had no
 * longer count, so it needs a new one. All or nothing.
 */
class ReplaceSecurityDevice
{
    public function __construct(private PhotoStorage $photoStorage) {}

    /**
     * Replace the device and return null, or return why it was not replaced, to show it on screen. The photo of a
     * replacement that did not happen is not kept.
     */
    public function handle(CertificationStep $step, SecurityDevice $removed, SecurityDevice $installed, string $reason, UploadedFile $photo, UploadedFile $thumbnail, User $user): ?string
    {
        $storedPhoto = $this->photoStorage->store($photo, $thumbnail, DiscardSecurityDevice::PHOTO_DIRECTORY);

        try {
            $refusal = DB::transaction(fn (): ?string => $this->replace($step, $removed, $installed, $reason, $storedPhoto, $user));
        } catch (Throwable $exception) {
            $this->photoStorage->delete($storedPhoto);

            throw $exception;
        }

        if ($refusal !== null) {
            $this->photoStorage->delete($storedPhoto);
        }

        return $refusal;
    }

    /**
     * Check the certification and both devices again, locked, and replace the device, or say why not.
     */
    private function replace(CertificationStep $step, SecurityDevice $removed, SecurityDevice $installed, string $reason, StoredPhoto $storedPhoto, User $user): ?string
    {
        $certification = Certification::query()->lockForUpdate()->findOrFail($step->certification_id);

        if ($certification->isAnnulled()) {
            return 'Se anuló la asignación de su kit: ya no admite reemplazos.';
        }

        if ($certification->isClosed()) {
            return 'La certificación está cerrada. Para reemplazar un dispositivo, reapertúrala desde Asignaciones.';
        }

        $lockedRemoved = SecurityDevice::query()->with('lot')->lockForUpdate()->findOrFail($removed->id);
        $lockedInstalled = SecurityDevice::query()->with(['lot', 'kits'])->lockForUpdate()->findOrFail($installed->id);
        $refusal = $this->refusal($certification, $step, $lockedRemoved, $lockedInstalled);

        if ($refusal !== null) {
            return $refusal;
        }

        /** @var BulkDispatch $bulkDispatch */
        $bulkDispatch = $lockedInstalled->bulkDispatches()->firstOrFail();

        $replacement = SecurityDeviceReplacement::query()->create([
            'certification_id' => $certification->id,
            'certification_step_id' => $step->id,
            'removed_device_id' => $lockedRemoved->id,
            'installed_device_id' => $lockedInstalled->id,
            'bulk_dispatch_id' => $bulkDispatch->id,
            'reason' => $reason,
            'replaced_by' => $user->id,
        ]);

        DB::table('certification_step_security_device')
            ->where('certification_step_id', $step->id)
            ->where('security_device_id', $lockedRemoved->id)
            ->update(['security_device_id' => $lockedInstalled->id, 'updated_at' => now()]);
        DB::table('kit_security_device')
            ->where('kit_id', $certification->kit_id)
            ->where('security_device_id', $lockedRemoved->id)
            ->update(['security_device_id' => $lockedInstalled->id, 'updated_at' => now()]);

        $lockedInstalled->update(['status' => $lockedRemoved->status]);
        $lockedRemoved->update([
            'status' => SecurityDeviceStatus::Discarded,
            'discarded_at' => now(),
            'discarded_by' => $user->id,
            'discard_reason' => $reason,
            'discard_photo_path' => $storedPhoto->path,
            'discard_thumbnail_path' => $storedPhoto->thumbnailPath,
        ]);

        $step->photos()->whereNull('superseded_by_replacement_id')->update(['superseded_by_replacement_id' => $replacement->id]);

        return null;
    }

    /**
     * Say why the device cannot be replaced by the extra device, or null: the device must still be in the section,
     * and the extra must be an unused extra device of this certification, of the same type (criteria 4 and 5).
     */
    private function refusal(Certification $certification, CertificationStep $step, SecurityDevice $removed, SecurityDevice $installed): ?string
    {
        $isInStep = DB::table('certification_step_security_device')
            ->where('certification_step_id', $step->id)
            ->where('security_device_id', $removed->id)
            ->exists();

        if (! $isInStep) {
            return sprintf('%s ya no está en la sección: quizá otro inspector lo reemplazó.', $removed->serial_number);
        }

        if (! $certification->extraDevices()->whereKey($installed->id)->exists()) {
            return sprintf('%s no es un dispositivo extra de esta unidad.', $installed->serial_number);
        }

        if ($installed->status !== SecurityDeviceStatus::Dispatched || $installed->kits->isNotEmpty()) {
            return sprintf('%s ya reemplazó a otro dispositivo. Elige otro extra.', $installed->serial_number);
        }

        if ($installed->lot->security_device_type_id !== $removed->lot->security_device_type_id) {
            return sprintf('%s es de otro tipo: el extra debe ser del mismo tipo que el dispositivo que sale.', $installed->serial_number);
        }

        return null;
    }
}
