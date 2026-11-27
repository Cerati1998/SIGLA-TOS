<?php

namespace App\Livewire\GpsDevices;

use App\Actions\GpsDevices\CheckGpsDeviceConnectivity;
use App\Models\GpsDevice;
use App\Services\Monitoring\MonitoringPlatformException;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Shows a GPS device with its last report in 24HM, and checks it again on demand (H42).
 */
class ConnectivityModal extends Component
{
    public bool $openModal = false;

    #[Locked]
    public ?int $gpsDeviceId = null;

    /**
     * Why the last check did not get the report, shown above it.
     */
    public ?string $failure = null;

    /**
     * Open the window for the given device, with the report it already has, without asking 24HM.
     */
    #[On('show-gps-device-connectivity')]
    public function open(int $gpsDeviceId): void
    {
        $this->gpsDeviceId = GpsDevice::withTrashed()->findOrFail($gpsDeviceId)->id;
        $this->failure = null;
        unset($this->gpsDevice);
        $this->openModal = true;
    }

    /**
     * Ask 24HM for the last report of the device and save it.
     */
    public function verify(CheckGpsDeviceConnectivity $checkConnectivity): void
    {
        $this->failure = null;

        try {
            $checkConnectivity->handle($this->gpsDevice());
        } catch (MonitoringPlatformException $exception) {
            $this->failure = $exception->getMessage();

            return;
        }

        unset($this->gpsDevice);
        $this->dispatch('toast', icon: 'success', title: 'Conectividad verificada', text: 'Se actualizó el último reporte del dispositivo.');
    }

    /**
     * Get the device shown, with what the window reads of it.
     */
    #[Computed]
    public function gpsDevice(): GpsDevice
    {
        return GpsDevice::withTrashed()->with(['gpsDeviceModel.brand', 'rentalProvider', 'branch'])->findOrFail($this->gpsDeviceId);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.gps-devices.connectivity-modal');
    }
}
