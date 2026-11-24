<div x-data="{ id: {{ $lot->id }}, name: @js($lot->name()) }" class="flex items-center gap-1 [&_a]:max-md:size-11 [&_button]:max-md:size-11">
    <x-wire-button primary :href="route('intranet.security-device-lots.show', $lot)" wire:navigate title="Ver lote" aria-label="Ver lote" xs>
        <i class="fa-solid fa-eye text-sm" aria-hidden="true"></i>
    </x-wire-button>

    <x-wire-button positive :href="route('intranet.security-device-lots.edit', $lot)" wire:navigate title="Editar lote" aria-label="Editar lote" xs>
        <i class="fa-solid fa-pen text-sm" aria-hidden="true"></i>
    </x-wire-button>

    @if ($canRemove)
        <x-wire-button
            negative
            x-on:click="confirmAction('¿Quitar este lote?', 'Se borran el lote ' + name + ' y sus dispositivos. No se deshace.', () => $wire.remove(id))"
            wire:loading.attr="disabled"
            wire:target="remove"
            title="Quitar lote"
            aria-label="Quitar lote"
            xs
        >
            <i class="fa-solid fa-trash text-sm" aria-hidden="true"></i>
        </x-wire-button>
    @endif
</div>
