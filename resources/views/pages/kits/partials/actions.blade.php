<div class="flex items-center gap-1 [&_a]:max-md:size-11">
    <x-wire-button primary :href="route('intranet.kits.show', $kit)" wire:navigate title="Abrir kit" aria-label="Abrir kit {{ $kit->pouch_code }}" xs>
        <i class="fa-solid fa-eye text-sm" aria-hidden="true"></i>
    </x-wire-button>
</div>
