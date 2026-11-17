@php
    $isFull = $kitSlot['added'] >= $kitSlot['quantity'];
    $inputId = 'slot-'.str_replace(':', '-', $kitSlot['key']);
    $label = \App\Actions\Kits\AddSecurityDeviceToKit::slotLabel($kitSlot);
@endphp

<div
    wire:key="slot-{{ $kitSlot['key'] }}"
    @class([
        'flex min-w-0 flex-col gap-2.5 rounded-card border p-3.5',
        'border-badge-success-ink/25 bg-badge-success' => $isFull,
        'border-line bg-white has-[input:focus]:border-brand has-[input:focus]:ring-4 has-[input:focus]:ring-logo-orange/20' => ! $isFull,
    ])
>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <label for="{{ $inputId }}" class="inline-flex flex-wrap items-center gap-2 font-heading text-base font-medium text-brand">
            {{ $kitSlot['name'] }}
            @if ($kitSlot['assignment'] !== null)
                <x-status-badge :variant="$kitSlot['assignment'] === \App\Enums\KitAssignment::External ? 'info' : 'muted'">{{ $kitSlot['assignment']->label() }}</x-status-badge>
            @endif
        </label>
        <span @class(['font-heading text-base font-medium whitespace-nowrap', 'text-badge-success-ink' => $isFull, 'text-brand' => ! $isFull])>
            {{ $kitSlot['added'] }} de {{ $kitSlot['quantity'] }}{{ $isFull ? ' · Completo' : '' }}
        </span>
    </div>

    @unless ($isFull)
        @include('pages.kits.partials.progress', ['label' => $label, 'added' => $kitSlot['added'], 'quantity' => $kitSlot['quantity']])
    @endunless

    @if ($canChange)
        <x-scan-input
            :id="$inputId"
            :disabled="$isFull"
            :placeholder="$isFull ? 'Completo' : 'Escanea o escribe'"
            data-slot-input="{{ $kitSlot['key'] }}"
            x-on:keydown.enter.prevent="submit('{{ $kitSlot['key'] }}', $el)"
        />
    @endif

    <template x-if="lastFor('{{ $kitSlot['key'] }}')">
        <p class="m-0 flex items-start gap-2 text-sm lg:hidden" :class="lastFor('{{ $kitSlot['key'] }}').state === 'added' ? 'text-badge-success-ink' : 'text-badge-danger-ink'" role="status">
            <strong class="font-semibold" x-text="lastFor('{{ $kitSlot['key'] }}').serialNumber"></strong>
            <span x-text="lastFor('{{ $kitSlot['key'] }}').message"></span>
        </p>
    </template>

    @if ($devices->isEmpty())
        <p class="m-0 text-sm text-ink-muted">Aún no hay series de este tipo.</p>
    @else
        <ul class="m-0 flex list-none flex-wrap gap-1.5 p-0" aria-label="Series de {{ $label }} en el kit">
            @foreach ($devices as $device)
                <li wire:key="device-{{ $device->id }}" class="inline-flex h-8 items-center gap-0.5 rounded-full border border-line bg-surface ps-2.5 pe-0.5 text-sm text-ink max-md:h-11 max-md:ps-3">
                    {{ $device->serial_number }}
                    @if ($canChange)
                    <button
                        type="button"
                        x-on:click="confirmAction('¿Quitar {{ $device->serial_number }} del kit?', 'Vuelve a quedar Disponible y el kit sigue Armando hasta completarse.', () => $wire.remove({{ $device->id }}))"
                        wire:loading.attr="disabled"
                        wire:target="remove"
                        title="Quitar del kit"
                        aria-label="Quitar del kit {{ $device->serial_number }}"
                        class="inline-flex size-7 cursor-pointer items-center justify-center rounded-full text-danger hover:bg-badge-danger disabled:opacity-50 max-md:size-10"
                    >
                        <i class="fa-solid fa-xmark text-sm" aria-hidden="true"></i>
                    </button>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
