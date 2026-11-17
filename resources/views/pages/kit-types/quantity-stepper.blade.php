@props([
    'deviceType',
    'field',
    'label',
    'min',
])

@php
    $max = \App\Livewire\Forms\KitTypeForm::MAX_QUANTITY;
    $value = "\$wire.form.devices[id].{$field}";
@endphp

<div class="flex items-center justify-between gap-2">
    <span class="text-sm font-medium text-badge-info-ink">{{ $label }}</span>
    <span class="inline-flex items-center overflow-hidden rounded-control border border-control bg-white">
        <button
            type="button"
            x-on:click="{{ $value }} = Math.max({{ $min }}, (parseInt({{ $value }}) || {{ $min }}) - 1)"
            x-bind:disabled="(parseInt($wire.form.devices[id]?.{{ $field }}) || {{ $min }}) <= {{ $min }}"
            class="inline-flex size-8 cursor-pointer items-center justify-center text-brand disabled:cursor-not-allowed disabled:text-control max-md:size-11"
            aria-label="Quitar uno de {{ $deviceType->name }}{{ $field === 'quantity' ? '' : ' ('.mb_strtolower($label).')' }}"
        >
            <i class="fa-solid fa-minus text-xs" aria-hidden="true"></i>
        </button>
        <input
            type="number"
            min="{{ $min }}"
            max="{{ $max }}"
            step="1"
            inputmode="numeric"
            wire:model="form.devices.{{ $deviceType->id }}.{{ $field }}"
            class="h-8 w-12 border-x border-line text-center font-heading text-base font-medium text-ink [appearance:textfield] focus:outline-none max-md:h-11 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
            aria-label="{{ $label }} de {{ $deviceType->name }}"
        >
        <button
            type="button"
            x-on:click="{{ $value }} = Math.min({{ $max }}, (parseInt({{ $value }}) || 0) + 1)"
            class="inline-flex size-8 cursor-pointer items-center justify-center text-brand max-md:size-11"
            aria-label="Agregar uno de {{ $deviceType->name }}{{ $field === 'quantity' ? '' : ' ('.mb_strtolower($label).')' }}"
        >
            <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
        </button>
    </span>
</div>

@error("form.devices.{$deviceType->id}.{$field}")
    <p class="text-sm text-danger">{{ $message }}</p>
@enderror
