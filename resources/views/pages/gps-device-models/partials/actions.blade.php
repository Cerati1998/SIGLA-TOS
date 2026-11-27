<div x-data="{ id: {{ $gpsDeviceModel->id }}, name: @js($gpsDeviceModel->name) }" class="flex items-center gap-1 [&_button]:max-md:size-11">
    <x-wire-button positive x-on:click="$dispatch('edit-gps-device-model', { gpsDeviceModelId: id })" title="Editar" aria-label="Editar" xs>
        <i class="fa-solid fa-pen text-sm" aria-hidden="true"></i>
    </x-wire-button>

    @if ($gpsDeviceModel->trashed())
        <x-wire-button primary x-on:click="$wire.reactivate(id)" wire:loading.attr="disabled" wire:target="deactivate,reactivate" title="Reactivar" aria-label="Reactivar" xs>
            <i class="fa-solid fa-rotate-left text-sm" aria-hidden="true"></i>
        </x-wire-button>
    @else
        <x-wire-button negative x-on:click="confirmAction('¿Desactivar ' + name + '?', 'Ya no se podrá elegir para un dispositivo GPS nuevo. Lo que ya lo usa lo conserva.', () => $wire.deactivate(id))" wire:loading.attr="disabled" wire:target="deactivate,reactivate" title="Desactivar" aria-label="Desactivar" xs>
            <i class="fa-solid fa-ban text-sm" aria-hidden="true"></i>
        </x-wire-button>
    @endif
</div>
