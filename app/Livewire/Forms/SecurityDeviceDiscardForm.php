<?php

namespace App\Livewire\Forms;

use App\Actions\SecurityDevices\DiscardSecurityDevice;
use App\Models\SecurityDevice;
use App\Models\User;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class SecurityDeviceDiscardForm extends Form
{
    /**
     * The longest reason, as its column.
     */
    public const int REASON_MAX = 255;

    public bool $openModal = false;

    #[Locked]
    public ?int $securityDeviceId = null;

    #[Locked]
    public string $serialNumber = '';

    public string $reason = '';

    /**
     * The photo of the device, already compressed in the browser (T77).
     */
    public ?TemporaryUploadedFile $photo = null;

    /**
     * The thumbnail of the photo, made in the browser.
     */
    public ?TemporaryUploadedFile $thumbnail = null;

    /**
     * Fill the form with the device to discard.
     */
    public function setSecurityDevice(SecurityDevice $device): void
    {
        $this->securityDeviceId = $device->id;
        $this->serialNumber = $device->serial_number;
    }

    /**
     * Get the validation rules of the form: the reason and the photo are required (T77).
     *
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:'.self::REASON_MAX],
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
            'photo.required' => 'Toma la foto del dispositivo.',
            'thumbnail.required' => 'Toma la foto del dispositivo.',
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
     * Discard the device with its photo. Return why it was not discarded, or null.
     */
    public function store(DiscardSecurityDevice $discardDevice, SecurityDevice $device, User $user): ?string
    {
        $this->reason = trim($this->reason);
        $this->validate();

        /** @var TemporaryUploadedFile $photo */
        $photo = $this->photo;
        /** @var TemporaryUploadedFile $thumbnail */
        $thumbnail = $this->thumbnail;

        return $discardDevice->handle($device, $this->reason, $photo, $thumbnail, $user);
    }
}
