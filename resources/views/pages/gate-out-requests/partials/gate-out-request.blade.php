@php
    $value = fn (?string ...$parts): string => implode(' · ', array_filter($parts, fn (?string $part): bool => $part !== null)) ?: '—';
    $dispatchedAt = $gateOutRequest['dispatched_at'] === null
        ? null
        : rescue(fn (): string => \Illuminate\Support\Carbon::createFromFormat('Y-m-d H:i:s', $gateOutRequest['dispatched_at'])->format('d/m/Y H:i'), $gateOutRequest['dispatched_at'], false);
    $sections = [
        '' => [
            'Cliente' => $value($gateOutRequest['client']['name']),
            'RUC del cliente' => $value($gateOutRequest['client']['document_number']),
            'Línea naviera' => $value($gateOutRequest['shipping_line']),
            'Producto' => $value($gateOutRequest['product']),
            'Booking' => $value($gateOutRequest['booking']),
            'Contenedor' => $value($gateOutRequest['container']),
            'Fecha de despacho' => $value($dispatchedAt),
        ],
        'Embarque' => [
            'Embarcador' => $value($gateOutRequest['shipper']['name']),
            'RUC del embarcador' => $value($gateOutRequest['shipper']['document_number']),
            'Nave y viaje' => $value($gateOutRequest['vessel'], $gateOutRequest['voyage']),
            'Destino' => $value($gateOutRequest['destination']),
        ],
        'Transporte' => [
            'Transportista' => $value($gateOutRequest['carrier']['name']),
            'RUC del transportista' => $value($gateOutRequest['carrier']['document_number']),
            'Conductor' => $value($gateOutRequest['driver']['name']),
            'DNI · Brevete' => $value($gateOutRequest['driver']['document_number'], $gateOutRequest['driver']['license']),
        ],
    ];
@endphp

<flux:card class="flex flex-col gap-5.25 p-5.25!">
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
        <h3 class="m-0 flex-[1_1_15rem] font-heading text-h3 font-normal">Solicitud {{ $gateOutRequest['reference'] }}</h3>
        <x-status-badge variant="info">Solo lectura · SIGLA 1.0</x-status-badge>
    </div>

    @foreach ($sections as $heading => $details)
        <section @class(['flex flex-col gap-3.5', 'border-t border-line pt-5.25' => $heading !== ''])>
            @if ($heading !== '')
                <h4 class="m-0 font-heading text-base font-medium">{{ $heading }}</h4>
            @endif

            <dl class="m-0 grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach ($details as $label => $detail)
                    <div class="flex min-w-0 flex-col gap-0.5">
                        <dt class="text-xs tracking-wider text-ink-muted uppercase">{{ $label }}</dt>
                        <dd class="m-0 break-words">{{ $detail }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endforeach

    <section class="flex flex-col gap-3.5 border-t border-line pt-5.25">
        <h4 class="m-0 font-heading text-base font-medium">Vehículos</h4>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @include('pages.gate-out-requests.partials.vehicle', ['kind' => 'tractor', 'title' => 'Remolcador', 'vehicle' => $gateOutRequest['tractor']])
            @include('pages.gate-out-requests.partials.vehicle', ['kind' => 'semi_trailer', 'title' => 'Semirremolque', 'vehicle' => $gateOutRequest['semi_trailer']])
        </div>
    </section>
</flux:card>
