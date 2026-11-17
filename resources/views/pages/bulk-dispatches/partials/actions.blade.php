<div class="flex items-center gap-1 [&_a]:max-md:size-11">
    <x-wire-button primary :href="route('intranet.bulk-dispatches.show', $bulkDispatch)" title="Ver detalle" aria-label="Ver detalle del despacho {{ $bulkDispatch->number() }}" xs>
        <i class="fa-solid fa-eye text-sm" aria-hidden="true"></i>
    </x-wire-button>
</div>
