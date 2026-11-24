<?php

namespace App\Livewire\SecurityDevices;

use App\Enums\SecurityDeviceStatus;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceLot;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Show the trace of the discard of a device of a lot: why, its photo, who discarded it and when (T77).
 *
 * @property-read SecurityDevice|null $device
 */
class DiscardDetailModal extends Component
{
    public bool $openModal = false;

    /**
     * The lot whose discarded devices are shown.
     */
    #[Locked]
    public int $lotId;

    /**
     * The discarded device shown, or null before the first one.
     */
    #[Locked]
    public ?int $securityDeviceId = null;

    /**
     * Fix the lot whose discarded devices are shown.
     */
    public function mount(SecurityDeviceLot $lot): void
    {
        $this->lotId = $lot->id;
    }

    /**
     * Open the window for the given discarded device of the lot.
     */
    #[On('show-security-device-discard')]
    public function open(int $securityDeviceId): void
    {
        $this->securityDeviceId = $securityDeviceId;
        unset($this->device);

        if ($this->device === null) {
            $this->dispatch('toast', icon: 'warning', title: 'Sin descarte', text: 'Ese dispositivo no está descartado.');

            return;
        }

        $this->openModal = true;
    }

    /**
     * Get the discarded device of the lot, with who discarded it. A device of another lot answers 404.
     */
    #[Computed]
    public function device(): ?SecurityDevice
    {
        if ($this->securityDeviceId === null) {
            return null;
        }

        $device = SecurityDevice::query()
            ->with('discarder')
            ->where('security_device_lot_id', $this->lotId)
            ->findOrFail($this->securityDeviceId);

        return $device->status === SecurityDeviceStatus::Discarded ? $device : null;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.security-device-lots.discard-detail-modal');
    }
}
