<?php

namespace App\Livewire\SecurityDevices;

use App\Models\SecurityDevice;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SerialNumberSearch extends Component
{
    public string $serialNumber = '';

    /**
     * The serial number of the last search, as it was searched.
     */
    #[Locked]
    public ?string $searchedSerialNumber = null;

    /**
     * The device found by the last search.
     */
    #[Locked]
    public ?int $deviceId = null;

    /**
     * Look up the device with the written or scanned serial number.
     */
    public function search(): void
    {
        $this->serialNumber = mb_strtoupper(trim($this->serialNumber));

        $this->validate(
            ['serialNumber' => ['required', 'string', 'max:50']],
            attributes: ['serialNumber' => 'serie'],
        );

        $this->searchedSerialNumber = $this->serialNumber;
        $this->deviceId = SecurityDevice::query()->where('serial_number', $this->serialNumber)->value('id');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.security-device-lots.serial-number-search', [
            'device' => $this->deviceId === null
                ? null
                : SecurityDevice::query()->with('lot.announcement')->find($this->deviceId),
        ]);
    }
}
