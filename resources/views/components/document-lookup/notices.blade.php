{{-- The notices of the document lookup: while it runs and how it ended. The lookup clears the last notice before it starts. --}}
<p wire:loading.flex wire:target="lookup" role="status" class="m-0 items-center gap-2 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
    <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
    Consultando el documento…
</p>

<div role="status" x-show="$wire.form.lookupStatus !== null" x-cloak>
    <p x-show="$wire.form.lookupStatus === 'found'" class="m-0 flex items-center gap-2 rounded-control bg-badge-success p-3.5 text-sm text-badge-success-ink">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        Datos completados. Puedes corregirlos antes de guardar.
    </p>
    <p x-show="$wire.form.lookupStatus === 'not_found'" class="m-0 flex items-center gap-2 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        No encontramos este documento. Llena los datos a mano para guardar.
    </p>
    <p x-show="$wire.form.lookupStatus === 'unavailable'" class="m-0 flex items-center gap-2 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        La consulta no está disponible en este momento. Vuelve a intentar con la lupa o llena los datos a mano para guardar.
    </p>
</div>
