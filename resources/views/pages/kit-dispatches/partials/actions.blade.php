<div class="flex items-center gap-1 [&_a]:max-md:size-11">
    <x-wire-button negative :href="route('intranet.kit-dispatches.pdf', $kitDispatch)" target="_blank" title="PDF del despacho" aria-label="PDF del despacho {{ $kitDispatch->number() }}" xs>
        <i class="fa-solid fa-file-pdf text-sm" aria-hidden="true"></i>
    </x-wire-button>
</div>
