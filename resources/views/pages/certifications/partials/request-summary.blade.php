@php
    $dispatchedAt = $gateOutRequest['dispatched_at'] === null
        ? null
        : rescue(fn (): string => \Illuminate\Support\Carbon::createFromFormat('Y-m-d H:i:s', $gateOutRequest['dispatched_at'])->format('d/m/Y H:i'), $gateOutRequest['dispatched_at'], false);
    $main = [
        'Cliente' => [$value($gateOutRequest['client']['name']), null],
        'Línea naviera' => $matchedShippingLine === null
            ? [$value($gateOutRequest['shipping_line']), null]
            : [$value($matchedShippingLine->code, $matchedShippingLine->name), 'En SIGLA 1.0: '.$gateOutRequest['shipping_line']],
        'Conductor' => [$value($gateOutRequest['driver']['name']), null],
    ];
    $more = [
        'Producto' => $value($gateOutRequest['product']),
        'Booking' => $value($gateOutRequest['booking']),
        'Contenedor' => $value($gateOutRequest['container']),
        'Fecha de despacho' => $value($dispatchedAt),
        'Transportista' => $value($gateOutRequest['carrier']['name']),
        'RUC del transportista' => $value($gateOutRequest['carrier']['document_number']),
        'DNI' => $value($gateOutRequest['driver']['document_number']),
        'Brevete' => $value($gateOutRequest['driver']['license']),
    ];
    $plates = [
        'Remolcador' => $gateOutRequest['tractor']['plate'],
        'Semirremolque' => $gateOutRequest['semi_trailer']['plate'],
    ];
@endphp

<section x-data="{ showAll: false }" class="flex flex-col gap-3.5 rounded-card border border-line p-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <strong class="font-heading text-lg font-medium text-brand">Solicitud {{ $gateOutRequest['reference'] }}</strong>
        <x-status-badge variant="info">Solo lectura · SIGLA 1.0</x-status-badge>
    </div>

    <dl class="m-0 grid grid-cols-2 gap-3 md:grid-cols-4">
        @foreach ($main as $label => [$detail, $origin])
            <div wire:key="request-main-{{ $loop->index }}" class="col-span-2 flex min-w-0 flex-col gap-0.5 md:col-span-1">
                <dt class="text-xs tracking-wider text-ink-muted uppercase">{{ $label }}</dt>
                <dd class="m-0 break-words">
                    {{ $detail }}
                    @if ($origin !== null)
                        <small class="block text-sm text-ink-muted">{{ $origin }}</small>
                    @endif
                </dd>
            </div>
        @endforeach
        @foreach ($plates as $label => $plate)
            <div wire:key="request-plate-{{ $loop->index }}" class="flex min-w-0 flex-col gap-1">
                <dt class="text-xs tracking-wider text-ink-muted uppercase">{{ $label }}</dt>
                <dd class="m-0">
                    @if ($plate === null)
                        —
                    @else
                        <span class="inline-flex flex-col items-center overflow-hidden rounded-control border-2 border-ink bg-white" aria-label="Placa {{ $plate }}">
                            <span class="w-full bg-brand text-center text-[9px] leading-snug tracking-[0.12em] text-white">PERÚ</span>
                            <span class="px-2.5 pb-0.5 font-heading text-lg font-medium tracking-widest">{{ $plate }}</span>
                        </span>
                    @endif
                </dd>
            </div>
        @endforeach
    </dl>

    <dl x-show="showAll" x-cloak class="m-0 grid grid-cols-2 gap-3 border-t border-line pt-3.5 md:grid-cols-4">
        @foreach ($more as $label => $detail)
            <div wire:key="request-more-{{ $loop->index }}" class="flex min-w-0 flex-col gap-0.5">
                <dt class="text-xs tracking-wider text-ink-muted uppercase">{{ $label }}</dt>
                <dd class="m-0 break-words">{{ $detail }}</dd>
            </div>
        @endforeach
    </dl>

    <button type="button" x-on:click="showAll = ! showAll" x-bind:aria-expanded="showAll" class="flex min-h-12 w-full items-center justify-between border-t border-line pt-1 text-base font-medium text-brand">
        <span x-text="showAll ? 'Ocultar los demás datos' : 'Ver todos los datos'"></span>
        <i class="fa-solid fa-chevron-down text-sm transition-transform" x-bind:class="showAll && 'rotate-180'" aria-hidden="true"></i>
    </button>
</section>
