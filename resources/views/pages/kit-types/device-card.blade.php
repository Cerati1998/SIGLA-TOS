@props([
    'idPrefix',
    'deviceType',
])

@php
    $inputId = "{$idPrefix}-device-{$deviceType->id}";
@endphp

<div
    {{ $attributes->only('wire:key') }}
    x-data="{ id: '{{ $deviceType->id }}' }"
    class="flex flex-col gap-2.5 rounded-card border p-3"
    x-bind:class="$wire.form.devices[id]?.on ? 'border-brand bg-badge-info ring-1 ring-brand' : '{{ $deviceType->trashed() ? 'border-dashed border-highlight bg-badge-warning' : 'border-line bg-white' }}'"
>
    <label for="{{ $inputId }}" class="flex cursor-pointer items-start gap-2.5">
        <span class="flex min-w-0 flex-1 flex-col gap-0.5">
            <span class="text-sm font-semibold text-ink">{{ $deviceType->name }}</span>
            <span class="text-xs text-ink-muted">{{ $deviceType->kind->label() }}</span>
        </span>

        <input id="{{ $inputId }}" type="checkbox" role="switch" wire:model="form.devices.{{ $deviceType->id }}.on" class="peer sr-only">
        <span aria-hidden="true" class="relative h-6 w-11 shrink-0 rounded-full bg-control transition-colors peer-checked:bg-brand peer-focus-visible:ring-2 peer-focus-visible:ring-brand peer-focus-visible:ring-offset-2 after:absolute after:top-0.75 after:left-0.75 after:size-4.5 after:rounded-full after:bg-white after:shadow-sm after:transition-transform peer-checked:after:translate-x-5"></span>
    </label>

    @if ($deviceType->trashed())
        <p class="text-xs text-badge-warning-ink">
            <x-status-badge variant="warning" class="me-1 py-0! text-xs!">Desactivado</x-status-badge>
            Se conserva mientras no lo apagues; si lo apagas, ya no podrás volver a elegirlo.
        </p>
    @endif

    <div x-show="$wire.form.devices[id]?.on" x-cloak class="flex items-center justify-between gap-2">
        <span class="text-sm font-medium text-badge-info-ink">Cantidad</span>
        <span class="inline-flex items-center overflow-hidden rounded-control border border-control bg-white">
            <button
                type="button"
                x-on:click="$wire.form.devices[id].quantity = Math.max(1, (parseInt($wire.form.devices[id].quantity) || 1) - 1)"
                x-bind:disabled="(parseInt($wire.form.devices[id]?.quantity) || 1) <= 1"
                class="inline-flex size-8 cursor-pointer items-center justify-center text-brand disabled:cursor-not-allowed disabled:text-control max-md:size-11"
                aria-label="Quitar uno de {{ $deviceType->name }}"
            >
                <i class="fa-solid fa-minus text-xs" aria-hidden="true"></i>
            </button>
            <input
                type="number"
                min="1"
                max="{{ \App\Livewire\Forms\KitTypeForm::MAX_QUANTITY }}"
                step="1"
                inputmode="numeric"
                wire:model="form.devices.{{ $deviceType->id }}.quantity"
                class="h-8 w-12 border-x border-line text-center font-heading text-base font-medium text-ink [appearance:textfield] focus:outline-none max-md:h-11 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                aria-label="Cantidad de {{ $deviceType->name }}"
            >
            <button
                type="button"
                x-on:click="$wire.form.devices[id].quantity = Math.min({{ \App\Livewire\Forms\KitTypeForm::MAX_QUANTITY }}, (parseInt($wire.form.devices[id].quantity) || 0) + 1)"
                class="inline-flex size-8 cursor-pointer items-center justify-center text-brand max-md:size-11"
                aria-label="Agregar uno de {{ $deviceType->name }}"
            >
                <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
            </button>
        </span>
    </div>

    <span x-show="! $wire.form.devices[id]?.on" class="text-sm text-ink-muted">No lo lleva</span>

    @error("form.devices.{$deviceType->id}.quantity")
        <p class="text-sm text-danger">{{ $message }}</p>
    @enderror
</div>
