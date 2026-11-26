<?php

namespace App\Livewire\Inspections;

use App\Actions\Certifications\ListExtraDevices;
use App\Actions\Reinspections\ReplaceReinspectionDevice;
use App\Models\ReinspectionDevice;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * A device to replace in a reopened section of a reinspection (H35, criterion 5): the incorrect series, the series of
 * the extra device that comes in, scanned or typed, and the required photo of the one that leaves, already compressed
 * in the browser. The server is called only to replace; then the inspection reloads its sections.
 *
 * @phpstan-import-type Extra from ListExtraDevices
 */
class ReinspectionDeviceForm extends Component
{
    use WithFileUploads;

    #[Locked]
    public ReinspectionDevice $reinspectionDevice;

    /**
     * The bulk dispatches of the unused extras of its type, to tell where the new one must come from.
     *
     * @var list<string>
     */
    #[Locked]
    public array $dispatchCodes = [];

    public string $serialNumber = '';

    /**
     * The photo of the device that leaves, already compressed in the browser.
     */
    public ?TemporaryUploadedFile $photo = null;

    /**
     * The thumbnail of the photo, made in the browser.
     */
    public ?TemporaryUploadedFile $thumbnail = null;

    /**
     * Replace the device and tell the inspection to reload its sections, or show why it was not replaced.
     */
    public function replace(ReplaceReinspectionDevice $replaceDevice): void
    {
        $this->serialNumber = mb_strtoupper(trim($this->serialNumber));

        $this->validate([
            'serialNumber' => ['required', 'string', 'max:50'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg', 'max:3072'],
            'thumbnail' => ['required', 'image', 'mimes:jpg,jpeg', 'max:512'],
        ], [
            'serialNumber.required' => 'Escanea o escribe la serie nueva.',
            'serialNumber.max' => 'La serie puede tener hasta 50 caracteres.',
            'photo.required' => 'Toma la foto del dispositivo que sale.',
            'thumbnail.required' => 'Toma la foto del dispositivo que sale.',
            'photo.*' => 'La foto no es válida. Tómala otra vez.',
            'thumbnail.*' => 'La foto no es válida. Tómala otra vez.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        /** @var TemporaryUploadedFile $photo */
        $photo = $this->photo;
        /** @var TemporaryUploadedFile $thumbnail */
        $thumbnail = $this->thumbnail;

        $refusal = $replaceDevice->handle($this->reinspectionDevice, $this->serialNumber, $photo, $thumbnail, $user);

        if ($refusal !== null) {
            $this->addError('serialNumber', $refusal);

            return;
        }

        $this->dispatch('security-device-replaced');
        $this->dispatch('toast', icon: 'success', title: 'Dispositivo reemplazado', text: "Entró {$this->serialNumber}; el anterior pasó a Descartado. Toma al menos una foto nueva de la sección.");
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.inspections.reinspection-device-form');
    }
}
