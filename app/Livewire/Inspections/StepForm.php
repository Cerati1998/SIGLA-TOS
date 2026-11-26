<?php

namespace App\Livewire\Inspections;

use App\Actions\Certifications\AddCertificationPhoto;
use App\Actions\Certifications\DeleteCertificationPhoto;
use App\Actions\Certifications\ListExtraDevices;
use App\Actions\Certifications\ReplaceCertificationPhoto;
use App\Enums\CertificationSectionType;
use App\Models\Certification;
use App\Models\CertificationPhoto;
use App\Models\CertificationStep;
use App\Models\Reinspection;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * The steps of the internal or the external inspection of a unit, one section at a time, where the inspector takes
 * its photos with the camera of the phone (H18). The photo arrives already compressed in the browser, with its
 * thumbnail; moving between the steps happens in the browser, and the server is called only to save or delete a
 * photo. A closed certification is only read, until it is reopened (H18, criterion 11). A device of a section can be
 * replaced by an extra device of the unit, in its own window (H38); then the photos the section had no longer count.
 * In reinspection (H35, criteria 4 to 6) the certification stays closed and only its reopened sections of this
 * inspection are shown and edited: each incorrect series is replaced, and then the section takes its new photos.
 *
 * @property-read Collection<int, CertificationStep> $steps
 * @property-read array{total: int, unusedCount: int, unusedTypeIds: list<int>} $extras
 * @property-read Reinspection|null $reinspection
 * @property-read array<int, list<string>> $extraDispatches
 */
class StepForm extends Component
{
    use WithFileUploads;

    #[Locked]
    public Certification $certification;

    #[Locked]
    public CertificationSectionType $type;

    /**
     * The photo uploaded last, compressed in the browser, waiting to be saved in a step.
     */
    public ?TemporaryUploadedFile $photo = null;

    /**
     * The thumbnail of the uploaded photo, made in the browser.
     */
    public ?TemporaryUploadedFile $thumbnail = null;

    /**
     * Get the sections of the inspection with their devices and photos, in their order.
     *
     * @return Collection<int, CertificationStep>
     */
    #[Computed]
    public function steps(): Collection
    {
        $reinspection = $this->reinspection;

        return $this->certification->steps()
            ->where('type', $this->type)
            ->when($reinspection !== null, fn ($steps) => $steps->whereKey($reinspection?->reopenedStepIds() ?? []))
            ->with(['section', 'devices.lot.securityDeviceType', 'photos.uploader', 'photos.supersedingReplacement', 'replacements.removedDevice'])
            ->get();
    }

    /**
     * Get the open reinspection of the unit when it reopened sections of this inspection (H35, criterion 4), with its
     * devices to replace.
     */
    #[Computed]
    public function reinspection(): ?Reinspection
    {
        $reinspection = $this->certification->openReinspection()
            ->with(['starter', 'devices.device.lot.securityDeviceType', 'devices.step'])
            ->first();

        if ($reinspection === null || $reinspection->devices->doesntContain(fn ($device): bool => $device->step->type === $this->type)) {
            return null;
        }

        return $reinspection;
    }

    /**
     * Get the bulk dispatches of the unused extra devices of each type, to tell the reinspection where the new device
     * must come from.
     *
     * @return array<int, list<string>>
     */
    #[Computed]
    public function extraDispatches(): array
    {
        $dispatches = [];

        foreach (app(ListExtraDevices::class)->handle($this->certification, $this->type)['extras'] as $extra) {
            if ($extra['unused']) {
                $dispatches[$extra['typeId']][] = $extra['dispatch'];
            }
        }

        return array_map(fn (array $codes): array => array_values(array_unique($codes)), $dispatches);
    }

    /**
     * Get how many extra devices the unit has and the types of the unused ones, which tell what can be replaced
     * (H38, criteria 3 and 4).
     *
     * @return array{total: int, unusedCount: int, unusedTypeIds: list<int>}
     */
    #[Computed]
    public function extras(): array
    {
        $extras = app(ListExtraDevices::class)->handle($this->certification, $this->type)['extras'];

        $unused = array_filter($extras, fn (array $extra): bool => $extra['unused']);

        return [
            'total' => count($extras),
            'unusedCount' => count($unused),
            'unusedTypeIds' => array_values(array_unique(array_map(fn (array $extra): int => $extra['typeId'], $unused))),
        ];
    }

    /**
     * Reload the sections after a device was replaced in the window of the extra devices (H38).
     */
    #[On('security-device-replaced')]
    public function refreshSteps(): void
    {
        unset($this->steps, $this->extras, $this->reinspection, $this->extraDispatches);
    }

    /**
     * Save the uploaded photo in a step, as a new one or in the place of one of its photos (criteria 5, 7 and 9).
     * It answers true to the browser, which then takes the photo out of its upload queue.
     */
    public function savePhoto(int $stepId, ?int $replacedPhotoId, AddCertificationPhoto $addPhoto, ReplaceCertificationPhoto $replacePhoto): bool
    {
        if ($this->refusedBecauseClosed($stepId)) {
            return false;
        }

        if ($this->hasDevicesToReplace($stepId)) {
            $this->dispatch('toast', icon: 'warning', title: 'Primero el dispositivo', text: 'Reemplaza la serie incorrecta antes de tomar las fotos nuevas.');

            return false;
        }

        $this->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg', 'max:3072'],
            'thumbnail' => ['required', 'image', 'mimes:jpg,jpeg', 'max:512'],
        ], [
            'photo.*' => 'La foto no es válida. Tómala otra vez.',
            'thumbnail.*' => 'La foto no es válida. Tómala otra vez.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $step = $this->step($stepId);
        /** @var TemporaryUploadedFile $photo */
        $photo = $this->photo;
        /** @var TemporaryUploadedFile $thumbnail */
        $thumbnail = $this->thumbnail;

        if ($replacedPhotoId === null) {
            $addPhoto->handle($step, $photo, $thumbnail, $user);
        } else {
            $replacePhoto->handle($this->photoOf($step, $replacedPhotoId), $photo, $thumbnail, $user);
        }

        $this->reset('photo', 'thumbnail');
        unset($this->steps);

        return true;
    }

    /**
     * Delete a photo of a step, once the inspector confirmed it (criterion 9).
     */
    public function deletePhoto(int $stepId, int $photoId, DeleteCertificationPhoto $deletePhoto): void
    {
        if ($this->refusedBecauseClosed($stepId)) {
            return;
        }

        $deletePhoto->handle($this->photoOf($this->step($stepId), $photoId));

        unset($this->steps);

        $this->dispatch('toast', icon: 'success', title: 'Foto borrada');
    }

    /**
     * Refuse a change when the certification was closed, even if the screen was opened before (H18, criterion 11), or
     * when the assignment of its kit was annulled (H32). In reinspection, a reopened section is edited (H35).
     */
    private function refusedBecauseClosed(int $stepId): bool
    {
        $certification = $this->certification->refresh();
        unset($this->reinspection);

        if ($certification->isAnnulled()) {
            $this->dispatch('toast', icon: 'warning', title: 'Asignación anulada', text: 'No se puede editar: se anuló la asignación de su kit.');

            return true;
        }

        if (! $certification->isClosed()) {
            return false;
        }

        if (in_array($stepId, $this->reinspection?->reopenedStepIds() ?? [], true)) {
            return false;
        }

        $this->dispatch('toast', icon: 'warning', title: 'Certificación cerrada', text: 'No se puede editar. Para corregir sus fotos, reapertúrala desde Asignaciones.');

        return true;
    }

    /**
     * Whether a reopened section still has an incorrect series to replace: its new photos come after it (H35).
     */
    private function hasDevicesToReplace(int $stepId): bool
    {
        return (bool) $this->reinspection?->devices()
            ->where('certification_step_id', $stepId)
            ->whereNull('security_device_replacement_id')
            ->exists();
    }

    /**
     * Get a step of this inspection of this certification.
     */
    private function step(int $stepId): CertificationStep
    {
        return $this->certification->steps()->where('type', $this->type)->findOrFail($stepId);
    }

    /**
     * Get a photo of a step.
     */
    private function photoOf(CertificationStep $step, int $photoId): CertificationPhoto
    {
        return $step->currentPhotos()->findOrFail($photoId);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.inspections.step-form');
    }
}
