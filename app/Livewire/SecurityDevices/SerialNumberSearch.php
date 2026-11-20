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
     * The devices found by the last search: more than one when types that do not use a series share it (T62).
     *
     * @var list<int>
     */
    #[Locked]
    public array $deviceIds = [];

    /**
     * Look up the devices with the written or scanned serial number.
     */
    public function search(): void
    {
        $this->serialNumber = mb_strtoupper(trim($this->serialNumber));

        $this->validate(
            ['serialNumber' => ['required', 'string', 'max:50']],
            attributes: ['serialNumber' => 'serie'],
        );

        $this->searchedSerialNumber = $this->serialNumber;
        $this->deviceIds = array_values(SecurityDevice::query()->where('serial_number', $this->serialNumber)->orderBy('id')->get(['id'])
            ->map(fn (SecurityDevice $device): int => $device->id)
            ->all());
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.security-device-lots.serial-number-search', [
            'devices' => $this->deviceIds === []
                ? collect()
                : SecurityDevice::query()->with(['lot.announcement.securityDeviceType', 'approval.approver'])->whereKey($this->deviceIds)->orderBy('id')->get(),
        ]);
    }
}
