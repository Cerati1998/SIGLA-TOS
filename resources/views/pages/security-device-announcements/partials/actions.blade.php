<div class="flex items-center gap-1 [&_a]:max-md:size-11">
    <x-wire-button primary :href="route('intranet.security-device-announcements.show', $announcement)" wire:navigate title="Ver anuncio" aria-label="Ver anuncio" xs>
        <i class="fa-solid fa-eye text-sm" aria-hidden="true"></i>
    </x-wire-button>

    <x-wire-button positive :href="route('intranet.security-device-announcements.edit', $announcement)" wire:navigate title="Editar anuncio" aria-label="Editar anuncio" xs>
        <i class="fa-solid fa-pen text-sm" aria-hidden="true"></i>
    </x-wire-button>
</div>
