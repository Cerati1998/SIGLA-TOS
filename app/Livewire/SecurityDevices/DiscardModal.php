<?php

namespace App\Livewire\SecurityDevices;

use App\Actions\SecurityDevices\DiscardSecurityDevice;
use App\Livewire\Forms\SecurityDeviceDiscardForm;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceLot;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Discard a device of a lot, with a reason (T65) and a photo taken with the camera (T77).
 */
class DiscardModal extends Component
{
    use WithFileUploads;

    public SecurityDeviceDiscardForm $form;

    /**
     * The lot whose devices are discarded.
     */
    #[Locked]
    public int $lotId;

    /**
     * Fix the lot whose devices are discarded.
     */
    public function mount(SecurityDeviceLot $lot): void
    {
        $this->lotId = $lot->id;
    }

    /**
     * Open the window for the given device of the lot.
     */
    #[On('discard-security-device')]
    public function open(int $securityDeviceId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setSecurityDevice($this->device($securityDeviceId));
        $this->form->openModal = true;
    }

    /**
     * Discard the device and reload the detail of the lot, with its counts and devices.
     */
    public function save(DiscardSecurityDevice $discardDevice): void
    {
        /** @var User $user */
        $user = Auth::user();
        $reason = $this->form->store($discardDevice, $this->device((int) $this->form->securityDeviceId), $user);

        if ($reason !== null) {
            $this->form->openModal = false;
            $this->dispatch('toast', icon: 'warning', title: 'No se descartó', text: $reason);

            return;
        }

        session()->flash('toast', [
            'icon' => 'success',
            'title' => 'Dispositivo descartado',
            'text' => sprintf('%s pasó a Descartado: ya no se agrega a un kit ni se despacha a granel.', $this->form->serialNumber),
        ]);

        $this->redirectRoute('intranet.security-device-lots.show', $this->lotId, navigate: true);
    }

    /**
     * Get the device of the lot. A device of another lot answers 404.
     */
    private function device(int $securityDeviceId): SecurityDevice
    {
        return SecurityDevice::query()->where('security_device_lot_id', $this->lotId)->findOrFail($securityDeviceId);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.security-device-lots.discard-modal');
    }
}
