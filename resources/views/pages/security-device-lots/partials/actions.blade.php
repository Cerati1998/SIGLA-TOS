<div class="flex items-center gap-1 [&_a]:max-md:size-11">
    <x-wire-button primary :href="route('intranet.security-device-lots.show', $lot)" wire:navigate title="Ver lote" aria-label="Ver lote" xs>
        <i class="fa-solid fa-eye text-sm" aria-hidden="true"></i>
    </x-wire-button>

    <x-wire-button positive :href="route('intranet.security-device-lots.edit', $lot)" wire:navigate title="Editar lote" aria-label="Editar lote" xs>
        <i class="fa-solid fa-pen text-sm" aria-hidden="true"></i>
    </x-wire-button>
</div>
