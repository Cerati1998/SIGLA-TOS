<div x-data="{ id: {{ $vehicleBody->id }}, name: @js($vehicleBody->name) }" class="flex items-center gap-1 [&_button]:max-md:size-11">
    <x-wire-button positive x-on:click="$dispatch('edit-vehicle-body', { vehicleBodyId: id })" title="Editar" aria-label="Editar" xs>
        <i class="fa-solid fa-pen text-sm" aria-hidden="true"></i>
    </x-wire-button>

    @if ($vehicleBody->trashed())
        <x-wire-button primary x-on:click="$wire.reactivate(id)" wire:loading.attr="disabled" wire:target="deactivate,reactivate" title="Reactivar" aria-label="Reactivar" xs>
            <i class="fa-solid fa-rotate-left text-sm" aria-hidden="true"></i>
        </x-wire-button>
    @else
        <x-wire-button negative x-on:click="confirmAction('¿Desactivar ' + name + '?', 'Ya no se podrá elegir al registrar vehículos. Los vehículos que la tienen la conservan.', () => $wire.deactivate(id))" wire:loading.attr="disabled" wire:target="deactivate,reactivate" title="Desactivar" aria-label="Desactivar" xs>
            <i class="fa-solid fa-ban text-sm" aria-hidden="true"></i>
        </x-wire-button>
    @endif
</div>
