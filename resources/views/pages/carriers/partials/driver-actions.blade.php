<div x-data="{ driverId: {{ $driver->id }}, linkId: {{ $driver->link_id }}, name: @js($driver->full_name) }" class="flex items-center gap-1 [&_button]:max-md:size-11">
    <x-wire-button positive x-on:click="$dispatch('edit-driver', { driverId: driverId })" title="Editar" aria-label="Editar" xs>
        <i class="fa-solid fa-pen text-sm" aria-hidden="true"></i>
    </x-wire-button>

    @if ($driver->link_deleted_at)
        <x-wire-button primary x-on:click="$wire.reactivate(linkId)" wire:loading.attr="disabled" wire:target="deactivate,reactivate" title="Reactivar" aria-label="Reactivar" xs>
            <i class="fa-solid fa-rotate-left text-sm" aria-hidden="true"></i>
        </x-wire-button>
    @else
        <x-wire-button negative x-on:click="confirmAction('¿Desactivar ' + name + ' en este transportista?', 'Seguirá activo en los demás transportistas.', () => $wire.deactivate(linkId))" wire:loading.attr="disabled" wire:target="deactivate,reactivate" title="Desactivar" aria-label="Desactivar" xs>
            <i class="fa-solid fa-ban text-sm" aria-hidden="true"></i>
        </x-wire-button>
    @endif
</div>
