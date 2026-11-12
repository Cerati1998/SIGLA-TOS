@php
    $types = $kitType->securityDeviceTypes->count();
@endphp

<div x-data="{ open: false }" class="flex flex-col gap-2 whitespace-normal">
    <button
        type="button"
        x-on:click="open = ! open"
        x-bind:aria-expanded="open"
        x-bind:class="open ? 'border-brand text-brand' : 'border-line text-ink'"
        class="inline-flex min-h-8.5 w-fit cursor-pointer items-center gap-2 rounded-control border bg-white px-2.5 text-sm max-md:min-h-11"
    >
        <span><span class="font-heading font-medium text-brand">{{ $kitType->totalDevices() }}</span> dispositivos · {{ $types }} {{ $types === 1 ? 'tipo' : 'tipos' }}</span>
        <x-feather name="chevron-down" class="size-3.5 transition-transform" x-bind:class="open && 'rotate-180'" />
    </button>

    <ul x-show="open" x-cloak class="flex max-w-md flex-wrap gap-1.5" aria-label="Contenido de {{ $kitType->name }}">
        @foreach ($kitType->securityDeviceTypes as $deviceType)
            <li @class([
                'inline-flex items-center gap-1.5 rounded-control px-2 py-0.5 text-[13px]',
                'bg-table-head text-ink' => ! $deviceType->trashed(),
                'bg-badge-warning text-badge-warning-ink' => $deviceType->trashed(),
            ])>
                <span class="font-heading font-medium text-brand">{{ $deviceType->pivot->quantity }}</span>
                {{ $deviceType->name }}{{ $deviceType->trashed() ? ' (desactivado)' : '' }}
            </li>
        @endforeach
    </ul>
</div>
