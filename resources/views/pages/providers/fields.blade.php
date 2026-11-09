@props([
    'idPrefix',
])

@php
    $documentTypes = \App\Livewire\Forms\ProviderForm::DOCUMENT_TYPES;
@endphp

<div class="grid gap-4 md:grid-cols-[200px_minmax(0,1fr)]">
    <x-wire-native-select id="{{ $idPrefix }}-identity" wire:model="form.identityId" label="Tipo de documento" :options="collect($documentTypes)->map(fn (array $type): string => $type['label'])" option-key-value required />

    {{-- The events go on this wrapper: WireUI copies x-on attributes to both the input and its container. --}}
    <div
        x-data="{ digits: @js(collect($documentTypes)->map(fn (array $type): int => $type['digits'])) }"
        x-init="$el.querySelector('input').inputMode = 'numeric'"
        x-on:input="$wire.form.lookupStatus = null; $event.target.value.trim().length === digits[$wire.form.identityId] && $nextTick(() => $wire.lookup())"
        x-on:focusout="$event.target.tagName === 'INPUT' && ! $el.contains($event.relatedTarget) && $event.target.value.trim() !== '' && $event.target.value.trim().length !== digits[$wire.form.identityId] && $wire.lookup()"
    >
        <x-wire-input id="{{ $idPrefix }}-document-number" wire:model="form.documentNumber" label="Número de documento" type="text" maxlength="11" required autocomplete="off">
            <x-slot:append class="py-0! pr-0!">
                <button
                    type="button"
                    x-on:click="$wire.lookup(true)"
                    wire:loading.attr="disabled"
                    wire:target="lookup"
                    title="Consultar documento"
                    aria-label="Consultar documento"
                    class="flex h-full w-10 cursor-pointer items-center justify-center rounded-r-control bg-highlight text-white disabled:cursor-not-allowed disabled:opacity-60 max-md:w-11"
                >
                    <i class="fa-solid fa-magnifying-glass text-sm" aria-hidden="true"></i>
                </button>
            </x-slot:append>
        </x-wire-input>
    </div>
</div>

<p wire:loading.flex wire:target="lookup" role="status" class="m-0 items-center gap-2 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
    <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
    Consultando el documento…
</p>

<div wire:loading.remove wire:target="lookup" role="status">
    <p x-show="$wire.form.lookupStatus === 'found'" x-cloak class="m-0 flex items-center gap-2 rounded-control bg-badge-success p-3.5 text-sm text-badge-success-ink">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        Datos completados. Puedes corregirlos antes de guardar.
    </p>
    <p x-show="$wire.form.lookupStatus === 'not_found'" x-cloak class="m-0 flex items-center gap-2 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        No encontramos este documento. Llena los datos a mano para guardar.
    </p>
    <p x-show="$wire.form.lookupStatus === 'unavailable'" x-cloak class="m-0 flex items-center gap-2 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        La consulta no está disponible en este momento. Vuelve a intentar con la lupa o llena los datos a mano para guardar.
    </p>
</div>

{{-- A disabled fieldset disables the input inside it while the document is being looked up. --}}
<fieldset wire:loading.attr="disabled" wire:target="lookup" class="m-0 min-w-0 border-0 p-0">
    <x-wire-input id="{{ $idPrefix }}-business-name" wire:model="form.businessName" label="Razón social" type="text" maxlength="255" required autocomplete="off" />
</fieldset>

<x-wire-input id="{{ $idPrefix }}-address" wire:model="form.address" label="Dirección (opcional)" type="text" maxlength="255" autocomplete="off" />

<div class="grid gap-4 md:grid-cols-2">
    <x-wire-input id="{{ $idPrefix }}-email" wire:model="form.email" label="Correo electrónico (opcional)" type="email" maxlength="255" autocomplete="off" />
    <x-wire-input id="{{ $idPrefix }}-phone" wire:model="form.phone" label="Teléfono (opcional)" type="tel" maxlength="20" autocomplete="off" />
</div>
