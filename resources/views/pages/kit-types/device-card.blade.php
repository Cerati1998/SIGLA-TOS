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

    <div x-show="$wire.form.devices[id]?.on" x-cloak class="flex flex-col gap-2">
        @if ($deviceType->splits_internal_external)
            <x-pages::kit-types.quantity-stepper :device-type="$deviceType" field="internal" label="Interna" min="0" />
            <x-pages::kit-types.quantity-stepper :device-type="$deviceType" field="external" label="Externa" min="0" />

            <p class="flex justify-between gap-2 border-t border-line pt-2 text-sm text-badge-info-ink">
                <span>Total</span>
                <span class="font-heading font-medium text-brand" x-text="(parseInt($wire.form.devices[id]?.internal) || 0) + (parseInt($wire.form.devices[id]?.external) || 0)"></span>
            </p>

            @error("form.devices.{$deviceType->id}")
                <p class="text-sm text-danger">{{ $message }}</p>
            @enderror
        @elseif ($deviceType->kind === \App\Enums\SecurityDeviceKind::Pouch)
            <p class="m-0 flex items-center justify-between gap-2">
                <span class="text-sm font-medium text-badge-info-ink">Cantidad</span>
                <x-status-badge variant="info">1 por kit</x-status-badge>
            </p>
        @else
            <x-pages::kit-types.quantity-stepper :device-type="$deviceType" field="quantity" label="Cantidad" min="1" />
        @endif
    </div>

    <span x-show="! $wire.form.devices[id]?.on" class="text-sm text-ink-muted">
        No lo lleva{{ $deviceType->splits_internal_external ? ' · se reparte entre interna y externa' : '' }}
    </span>
</div>
