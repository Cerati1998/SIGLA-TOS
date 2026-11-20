<?php

namespace App\Livewire\Inspections;

use App\Actions\Certifications\AddCertificationPhoto;
use App\Actions\Certifications\DeleteCertificationPhoto;
use App\Actions\Certifications\ReplaceCertificationPhoto;
use App\Enums\CertificationSectionType;
use App\Models\Certification;
use App\Models\CertificationPhoto;
use App\Models\CertificationStep;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * The steps of the internal or the external inspection of a unit, one section at a time, where the inspector takes
 * its photos with the camera of the phone (H18). The photo arrives already compressed in the browser, with its
 * thumbnail; moving between the steps happens in the browser, and the server is called only to save or delete a
 * photo. A closed certification is only read, until it is reopened (H18, criterion 11).
 *
 * @property-read Collection<int, CertificationStep> $steps
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
        return $this->certification->steps()
            ->where('type', $this->type)
            ->with(['section', 'devices.lot.announcement.securityDeviceType', 'photos.uploader'])
            ->get();
    }

    /**
     * Save the uploaded photo in a step, as a new one or in the place of one of its photos (criteria 5, 7 and 9).
     * It answers true to the browser, which then takes the photo out of its upload queue.
     */
    public function savePhoto(int $stepId, ?int $replacedPhotoId, AddCertificationPhoto $addPhoto, ReplaceCertificationPhoto $replacePhoto): bool
    {
        if ($this->refusedBecauseClosed()) {
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
        if ($this->refusedBecauseClosed()) {
            return;
        }

        $deletePhoto->handle($this->photoOf($this->step($stepId), $photoId));

        unset($this->steps);

        $this->dispatch('toast', icon: 'success', title: 'Foto borrada');
    }

    /**
     * Refuse a change when the certification was closed, even if the screen was opened before (H18, criterion 11), or
     * when the assignment of its kit was annulled (H32).
     */
    private function refusedBecauseClosed(): bool
    {
        $certification = $this->certification->refresh();

        if ($certification->isAnnulled()) {
            $this->dispatch('toast', icon: 'warning', title: 'Asignación anulada', text: 'No se puede editar: se anuló la asignación de su kit.');

            return true;
        }

        if (! $certification->isClosed()) {
            return false;
        }

        $this->dispatch('toast', icon: 'warning', title: 'Certificación cerrada', text: 'No se puede editar. Para corregir sus fotos, reapertúrala desde Asignaciones.');

        return true;
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
        return $step->photos()->findOrFail($photoId);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.inspections.step-form');
    }
}
