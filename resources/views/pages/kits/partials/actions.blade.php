<div class="flex items-center gap-1 [&_a]:max-md:size-11">
    <x-wire-button primary :href="route('intranet.kits.show', $kit)" wire:navigate title="Abrir kit" aria-label="Abrir kit {{ $kit->pouch_code }}" xs>
        <i class="fa-solid fa-eye text-sm" aria-hidden="true"></i>
    </x-wire-button>
    @if ($kit->status->hasContentsPdf())
        <x-wire-button negative :href="route('intranet.kits.pdf', $kit)" target="_blank" title="PDF del kit" aria-label="PDF del kit {{ $kit->pouch_code }}" xs>
            <i class="fa-solid fa-file-pdf text-sm" aria-hidden="true"></i>
        </x-wire-button>
    @endif
</div>
