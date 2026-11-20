<?php

namespace App\Livewire\Forms;

use App\Actions\SecurityDevices\DiscardSecurityDevice;
use App\Models\SecurityDevice;
use App\Models\User;
use Livewire\Attributes\Locked;
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
     * Fill the form with the device to discard.
     */
    public function setSecurityDevice(SecurityDevice $device): void
    {
        $this->securityDeviceId = $device->id;
        $this->serialNumber = $device->serial_number;
    }

    /**
     * Get the validation rules of the form.
     *
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:'.self::REASON_MAX],
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
     * Discard the device. Return why it was not discarded, or null.
     */
    public function store(DiscardSecurityDevice $discardDevice, SecurityDevice $device, User $user): ?string
    {
        $this->reason = trim($this->reason);
        $this->validate();

        return $discardDevice->handle($device, $this->reason, $user);
    }
}
