<?php

namespace App\Livewire\BulkDispatches;

use App\Actions\BulkDispatches\DispatchBulkDevices;
use App\Actions\BulkDispatches\FindDeviceToBulkDispatch;
use App\Actions\BulkDispatches\GroupSecurityDevices;
use App\Actions\BulkDispatches\SelectBulkDispatchDevices;
use App\Enums\BulkDispatchReason;
use App\Livewire\Forms\BulkDispatchForm;
use App\Models\SecurityDeviceType;
use App\Models\User;
use App\Models\YardZone;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * The window «Despacho a granel» (H30): who receives, the reason with its client or kit, the yard zone, and the
 * devices chosen by scanning, by range or by quantity, dispatched in a single step.
 */
class CreateModal extends Component
{
    public BulkDispatchForm $form;

    /**
     * Add the device with the scanned or written serial number, of the type when one is chosen (T62). The answer feeds
     * the list of the last scans.
     *
     * @return array{ok: bool, message: string}
     */
    public function scan(FindDeviceToBulkDispatch $findDevice, string $serialNumber, string $typeId = ''): array
    {
        $serialNumber = mb_strtoupper(trim($serialNumber));

        if ($serialNumber === '' || mb_strlen($serialNumber) > 50) {
            return ['ok' => false, 'message' => 'Escanea o escribe una serie de hasta 50 caracteres.'];
        }

        $device = $findDevice->handle($serialNumber, $this->chosenType($typeId));

        if (is_string($device)) {
            return ['ok' => false, 'message' => $device];
        }

        if ($this->form->addDevices([$device->id]) === 0) {
            return ['ok' => true, 'message' => 'Ya estaba en este despacho.'];
        }

        $this->form->resetValidation('deviceIds');

        return ['ok' => true, 'message' => $device->lot->announcement->securityDeviceType->name.' · agregado'];
    }

    /**
     * Preview the devices from the first serial number to the last one, of the type when one is chosen (T62), or, with
     * $add, add them to the dispatch.
     *
     * @return array{ok: bool, message: string, count: int}
     */
    public function byRange(SelectBulkDispatchDevices $selectDevices, string $firstSerialNumber, string $lastSerialNumber, bool $add = false, string $typeId = ''): array
    {
        $first = mb_strtoupper(trim($firstSerialNumber));
        $last = mb_strtoupper(trim($lastSerialNumber));

        if ($first === '' || $last === '') {
            return ['ok' => false, 'message' => 'Escribe la serie inicial y la final.', 'count' => 0];
        }

        return $this->answer($selectDevices->byRange($first, $last, $this->chosenType($typeId)), $add);
    }

    /**
     * Get the type chosen to look the serial numbers up, or null for all of them.
     */
    private function chosenType(string $typeId): ?SecurityDeviceType
    {
        return ctype_digit($typeId) ? SecurityDeviceType::query()->find((int) $typeId) : null;
    }

    /**
     * Preview the given quantity of devices of the type from the first serial number or, with $add, add them to the
     * dispatch.
     *
     * @return array{ok: bool, message: string, count: int}
     */
    public function byQuantity(SelectBulkDispatchDevices $selectDevices, string $typeId, string $firstSerialNumber, string $quantity, bool $add = false): array
    {
        $type = SecurityDeviceType::query()->find((int) $typeId);
        $first = mb_strtoupper(trim($firstSerialNumber));

        if ($type === null || $first === '' || ! ctype_digit(trim($quantity))) {
            return ['ok' => false, 'message' => 'Elige el tipo y escribe la serie inicial y la cantidad.', 'count' => 0];
        }

        return $this->answer($selectDevices->byQuantity($type, $first, (int) trim($quantity), $this->form->chosenDeviceIds()), $add);
    }

    /**
     * Take a stretch of devices out of the dispatch.
     */
    public function removeStretch(GroupSecurityDevices $groupDevices, string $key): void
    {
        $stretch = collect($groupDevices->stretches($this->form->chosenDeviceIds()))->firstWhere('key', $key);

        if ($stretch !== null) {
            $this->form->removeDevices($stretch['deviceIds']);
        }
    }

    /**
     * Check the window and, when it is right, ask the user to confirm the dispatch.
     */
    public function confirmDispatch(): void
    {
        $this->form->validate();

        $count = count($this->form->chosenDeviceIds());

        $this->dispatch(
            'confirm-bulk-dispatch',
            title: sprintf('¿Despachar %d %s?', $count, $count === 1 ? 'dispositivo' : 'dispositivos'),
            text: 'Pasan a Despachado y ya no se pueden agregar a un kit.',
        );
    }

    /**
     * Dispatch the devices, close the window and refresh the list of bulk dispatches.
     */
    public function save(DispatchBulkDevices $dispatchBulkDevices): void
    {
        /** @var User $user */
        $user = Auth::user();
        $dispatch = $this->form->store($dispatchBulkDevices, $user)->load('receiver');
        $count = $dispatch->devices()->count();

        $this->discard();

        $this->dispatch(
            'toast',
            icon: 'success',
            title: sprintf('Despacho %s registrado', $dispatch->number()),
            text: sprintf('%d %s a %s.', $count, $count === 1 ? 'dispositivo despachado' : 'dispositivos despachados', $dispatch->receiver->full_name),
        );
        $this->dispatch('refreshDatatable');
    }

    /**
     * Forget what was chosen in a window that was closed without dispatching: nothing stays saved.
     */
    public function discard(): void
    {
        $this->form->reset();
        $this->form->resetValidation();
    }

    /**
     * Turn the devices chosen by range or by quantity into the answer for the screen, adding them when asked.
     *
     * @param  array{deviceIds: list<int>, error: string|null, summary: string|null}  $selection
     * @return array{ok: bool, message: string, count: int}
     */
    private function answer(array $selection, bool $add): array
    {
        if ($selection['error'] !== null) {
            return ['ok' => false, 'message' => $selection['error'], 'count' => 0];
        }

        $count = count($selection['deviceIds']);

        if (! $add) {
            return ['ok' => true, 'message' => (string) $selection['summary'], 'count' => $count];
        }

        $added = $this->form->addDevices($selection['deviceIds']);
        $this->form->resetValidation('deviceIds');

        return [
            'ok' => true,
            'message' => $added === $count
                ? sprintf('Agregados: %s.', $selection['summary'])
                : sprintf('Agregados %d; los otros %d ya estaban en este despacho.', $added, $count - $added),
            'count' => $added,
        ];
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $now = now('America/Lima');

        return view('pages.bulk-dispatches.create-modal', [
            'stretches' => app(GroupSecurityDevices::class)->stretches($this->form->chosenDeviceIds()),
            'deviceCount' => count($this->form->chosenDeviceIds()),
            'reasons' => BulkDispatchReason::cases(),
            'yardZones' => YardZone::query()->where('branch_id', $user->branch_id)->orderBy('name')->pluck('name', 'id'),
            'deviceTypes' => SecurityDeviceType::query()->orderBy('name')->pluck('name', 'id'),
            'deliverer' => sprintf('%s · %s · %s', $user->name, $now->format('d/m/Y'), $now->format('o-\WW')),
        ]);
    }
}
