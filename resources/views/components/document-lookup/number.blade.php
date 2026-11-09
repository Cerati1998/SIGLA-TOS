@props([
    'id',
    'digits',
    'maxlength',
])

{{--
    Document number with the magnifier button. It looks the document up when its digits are complete and warns of a wrong
    format when the field is left. `digits` maps each document type that can be looked up to its digits; for any other type
    the button is hidden and nothing is looked up.
    The events go on the wrapper: WireUI copies x-on attributes to both the input and its container.
--}}
<div
    x-data="{ digits: @js($digits) }"
    x-init="$el.querySelector('input').inputMode = 'numeric'"
    x-on:input="$wire.form.lookupStatus = null; digits[$wire.form.identityId] && $event.target.value.trim().length === digits[$wire.form.identityId] && $nextTick(() => $wire.lookup())"
    x-on:focusout="digits[$wire.form.identityId] && $event.target.tagName === 'INPUT' && ! $el.contains($event.relatedTarget) && $event.target.value.trim() !== '' && $event.target.value.trim().length !== digits[$wire.form.identityId] && $wire.lookup()"
>
    <x-wire-input id="{{ $id }}" wire:model="form.documentNumber" label="Número de documento" type="text" maxlength="{{ $maxlength }}" required autocomplete="off">
        <x-slot:append class="py-0! pr-0!" x-bind:hidden="! digits[$wire.form.identityId]">
            <button
                type="button"
                x-on:click="$wire.form.lookupStatus = null; $wire.lookup(true)"
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
