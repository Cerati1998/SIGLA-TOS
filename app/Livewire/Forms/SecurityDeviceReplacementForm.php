<?php

namespace App\Livewire\Forms;

use App\Actions\Certifications\ReplaceSecurityDevice;
use App\Enums\CertificationSectionType;
use App\Models\Certification;
use App\Models\CertificationStep;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceReplacement;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

/**
 * The window that replaces a device of the kit by an extra device (H38, criterion 4): the device that leaves, the extra
 * that comes in, the reason and the photo of the device that leaves, already compressed in the browser (T77). The
 * browser only offers the devices of this inspection and the unused extras of the same type; the server checks them
 * again when saving.
 */
class SecurityDeviceReplacementForm extends Form
{
    public bool $openModal = false;

    public ?int $removedDeviceId = null;

    public ?int $installedDeviceId = null;

    public string $reason = '';

    /**
     * The photo of the device that leaves, already compressed in the browser.
     */
    public ?TemporaryUploadedFile $photo = null;

    /**
     * The thumbnail of the photo, made in the browser.
     */
    public ?TemporaryUploadedFile $thumbnail = null;

    /**
     * Get the validation rules of the form: both devices, the reason and the photo are required.
     *
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'removedDeviceId' => ['required', 'integer'],
            'installedDeviceId' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:'.SecurityDeviceReplacement::REASON_MAX],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg', 'max:3072'],
            'thumbnail' => ['required', 'image', 'mimes:jpg,jpeg', 'max:512'],
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'removedDeviceId.required' => 'Elige el dispositivo del kit que sale.',
            'installedDeviceId.required' => 'Elige el dispositivo extra que entra.',
            'reason.required' => 'Escribe el motivo del reemplazo.',
            'photo.required' => 'Toma la foto del dispositivo que sale.',
            'thumbnail.required' => 'Toma la foto del dispositivo que sale.',
            'photo.*' => 'La foto no es válida. Tómala otra vez.',
            'thumbnail.*' => 'La foto no es válida. Tómala otra vez.',
        ];
    }

    /**
     * Get the names of the fields in the validation messages.
     *
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'reason' => 'motivo',
        ];
    }

    /**
     * Replace the device of a section of this inspection. Return why it was not replaced, or null.
     */
    public function store(ReplaceSecurityDevice $replaceDevice, Certification $certification, CertificationSectionType $type, User $user): ?string
    {
        $this->reason = trim($this->reason);
        $this->validate();

        $step = $this->stepOf($certification, $type);
        /** @var TemporaryUploadedFile $photo */
        $photo = $this->photo;
        /** @var TemporaryUploadedFile $thumbnail */
        $thumbnail = $this->thumbnail;

        return $replaceDevice->handle(
            $step,
            SecurityDevice::query()->findOrFail($this->removedDeviceId),
            SecurityDevice::query()->findOrFail($this->installedDeviceId),
            $this->reason,
            $photo,
            $thumbnail,
            $user,
        );
    }

    /**
     * Get the section of this inspection where the device that leaves is placed.
     *
     * @throws ValidationException When it is not placed in a section of this inspection.
     */
    private function stepOf(Certification $certification, CertificationSectionType $type): CertificationStep
    {
        $step = $certification->steps()
            ->where('type', $type)
            ->whereHas('devices', fn ($devices) => $devices->whereKey($this->removedDeviceId))
            ->first();

        if ($step === null) {
            throw ValidationException::withMessages(['form.removedDeviceId' => 'Ese dispositivo no está en una sección de esta inspección.']);
        }

        return $step;
    }
}
