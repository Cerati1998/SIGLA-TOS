<?php

namespace App\Livewire\Kits;

use App\Actions\Kits\AddSecurityDeviceToKit;
use App\Actions\Kits\RemoveSecurityDeviceFromKit;
use App\Enums\KitAssignment;
use App\Models\Kit;
use App\Models\SecurityDevice;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class DeviceScanner extends Component
{
    public Kit $kit;

    /**
     * Add the scanned or written serial number to a slot of the kit. The answer feeds the list of the last scans.
     *
     * @return array{ok: bool, message: string}
     */
    public function add(AddSecurityDeviceToKit $addDevice, string $slotKey, string $serialNumber): array
    {
        $serialNumber = mb_strtoupper(trim($serialNumber));

        if ($serialNumber === '' || mb_strlen($serialNumber) > 50) {
            return ['ok' => false, 'message' => 'Escanea o escribe una serie de hasta 50 caracteres.'];
        }

        $reason = $addDevice->handle($this->kit, $slotKey, $serialNumber);

        if ($reason !== null) {
            return ['ok' => false, 'message' => $reason];
        }

        $this->kit->refresh();
        $slot = collect($this->kit->slots())->firstWhere('key', $slotKey);

        return ['ok' => true, 'message' => ($slot === null ? 'Dispositivo' : AddSecurityDeviceToKit::slotLabel($slot)).' · agregado'];
    }

    /**
     * Take a device out of the kit: it becomes available again.
     */
    public function remove(RemoveSecurityDeviceFromKit $removeDevice, int $securityDeviceId): void
    {
        $serialNumber = SecurityDevice::query()->whereKey($securityDeviceId)->value('serial_number');
        $reason = $removeDevice->handle($this->kit, $securityDeviceId);

        if ($reason !== null) {
            $this->dispatch('toast', icon: 'warning', title: 'No se quitó', text: $reason);

            return;
        }

        $this->kit->refresh();
        $this->dispatch('toast', icon: 'success', title: 'Dispositivo quitado', text: sprintf('%s volvió a quedar Disponible.', $serialNumber));
    }

    /**
     * Get the devices of the kit grouped by slot key.
     *
     * @return Collection<int|string, Collection<int, SecurityDevice>>
     */
    private function devicesBySlot(): Collection
    {
        return $this->kit->devices()->with('lot.announcement')->get()->toBase()->groupBy(function (SecurityDevice $device): string {
            /** @var object{assignment: string|null} $pivot */
            $pivot = $device->getRelationValue('pivot');

            return Kit::slotKey($device->lot->announcement->security_device_type_id, KitAssignment::tryFrom((string) $pivot->assignment));
        });
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $kitSlots = $this->kit->slots();
        $this->kit->loadMissing('dispatches.receiver');

        return view('pages.kits.device-scanner', [
            'canChange' => $this->kit->status->contentsCanChange(),
            'kitDispatch' => $this->kit->dispatch(),
            'kitSlots' => $kitSlots,
            'devicesBySlot' => $this->devicesBySlot(),
            'totalQuantity' => array_sum(array_column($kitSlots, 'quantity')),
            'totalAdded' => array_sum(array_column($kitSlots, 'added')),
            'missingSlots' => array_values(array_filter($kitSlots, fn (array $slot): bool => $slot['added'] < $slot['quantity'])),
        ]);
    }
}
