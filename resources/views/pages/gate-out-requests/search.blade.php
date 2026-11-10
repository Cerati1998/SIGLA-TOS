@php
    $uppercase = 'const caret = $el.selectionStart; $el.value = $el.value.toUpperCase(); $el.setSelectionRange(caret, caret)';
@endphp

<div class="flex flex-col gap-5.25">
    <flux:card class="p-5.25!">
        <form wire:submit="search" class="flex flex-col gap-3.5" novalidate>
            <div class="grid grid-cols-1 items-start gap-x-4 gap-y-3.5 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
                <flux:input
                    wire:model="booking"
                    x-on:input="{{ $uppercase }}"
                    label="Booking"
                    type="text"
                    maxlength="50"
                    required
                    autocomplete="off"
                />

                <flux:input
                    wire:model="container"
                    x-on:input="{{ $uppercase }}"
                    label="Contenedor"
                    type="text"
                    maxlength="50"
                    required
                    autocomplete="off"
                />

                <flux:button type="submit" variant="primary" class="md:mt-6.5 max-md:w-full">
                    <i class="fa-solid fa-magnifying-glass text-sm" aria-hidden="true"></i>
                    <span wire:loading.remove wire:target="search">Consultar</span>
                    <span wire:loading wire:target="search">Consultando…</span>
                </flux:button>
            </div>

            <span class="text-sm text-ink-muted">Los dos son obligatorios. Se pasan a mayúsculas mientras escribes.</span>
        </form>
    </flux:card>

    <p wire:loading.flex wire:target="search" role="status" class="m-0 items-center gap-2 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
        <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
        Consultando SIGLA 1.0…
    </p>

    <div wire:loading.remove wire:target="search" class="flex flex-col gap-5.25">
        @switch($status)
            @case(\App\Services\Sigla1LookupStatus::Found)
                @if (count($gateOutRequests) > 1)
                    <p role="status" class="m-0 flex items-center gap-2 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
                        <x-feather name="info" class="size-4.5" />
                        Hay {{ count($gateOutRequests) }} solicitudes de retiro con este booking y este contenedor.
                    </p>
                @endif

                @foreach ($gateOutRequests as $gateOutRequest)
                    @include('pages.gate-out-requests.partials.gate-out-request', ['gateOutRequest' => $gateOutRequest])
                @endforeach
                @break

            @case(\App\Services\Sigla1LookupStatus::NotFound)
                <p role="status" class="m-0 flex items-center gap-2 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
                    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                    No hay ninguna solicitud de retiro con este booking y este contenedor. Revisa los dos datos.
                </p>
                @break

            @case(\App\Services\Sigla1LookupStatus::Unavailable)
                <div role="status" class="flex flex-col items-start gap-2.5 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                        SIGLA 1.0 no responde en este momento. Vuelve a intentar en unos minutos.
                    </span>
                    <flux:button wire:click="search" wire:loading.attr="disabled" wire:target="search" class="max-md:w-full">
                        <i class="fa-solid fa-rotate-right text-sm" aria-hidden="true"></i>
                        Reintentar
                    </flux:button>
                </div>
                @break

            @default
                <p class="m-0 flex items-center gap-2 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
                    <x-feather name="info" class="size-4.5" />
                    Escribe el booking y el contenedor para ver los datos de la solicitud.
                </p>
        @endswitch
    </div>
</div>
