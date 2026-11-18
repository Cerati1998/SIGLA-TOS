@php
    use App\Services\Sigla1LookupStatus;

    $value = fn (?string ...$parts): string => implode(' · ', array_filter($parts, fn (?string $part): bool => $part !== null)) ?: '—';
    $fromSigla1 = $gateOutRequest === null ? [] : [
        'Cliente' => $value($gateOutRequest['client']['name']),
        'Producto' => $value($gateOutRequest['product']),
        'DNI' => $value($gateOutRequest['driver']['document_number']),
        'Brevete' => $value($gateOutRequest['driver']['license']),
    ];
    $kept = [
        'Booking' => $certification->booking,
        'Línea naviera' => $certification->shippingLine->code.' · '.$certification->shippingLine->name,
        'Transportista' => $value($certification->carrier?->business_name),
        'Conductor' => $value($certification->driver?->full_name),
        'Remolcador' => $value($certification->tractor?->plate),
        'Semirremolque' => $value($certification->semiTrailer?->plate),
    ];
    $kit = [
        'Funda' => $certification->kit->pouch_code,
        'Formato' => $value($certification->inspection_sheet_code),
        'Tipo de kit' => $certification->kit->kitType->name,
        'Dispositivos' => (string) $certification->kit->devices->count(),
    ];
@endphp

<div class="flex flex-col gap-5.25">
    <p class="m-0 flex items-start gap-2 rounded-control bg-badge-success p-3.5 text-sm text-badge-success-ink">
        <i class="fa-solid fa-circle-check mt-0.5" aria-hidden="true"></i>
        <span>
            Iniciada el {{ $certification->started_at->format('d/m/Y') }} a las {{ $certification->started_at->format('H:i') }}
            por <strong>{{ $certification->certifier->name }}</strong>, en la zona <strong>{{ $certification->yardZone->name }}</strong>.
        </span>
    </p>

    <flux:card class="flex flex-col gap-5.25 p-5.25! max-md:p-4!">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
            <h3 class="m-0 flex-[1_1_15rem] font-heading text-h3 font-normal">Solicitud {{ $certification->gate_out_request_reference }}</h3>
            <x-status-badge variant="info">Guardada en SIGLA 2.0</x-status-badge>
        </div>

        <dl class="m-0 grid grid-cols-2 gap-4 md:grid-cols-3">
            @foreach ($kept as $label => $detail)
                <div wire:key="kept-{{ $loop->index }}" class="flex min-w-0 flex-col gap-0.5">
                    <dt class="text-xs tracking-wider text-ink-muted uppercase">{{ $label }}</dt>
                    <dd class="m-0 break-words">{{ $detail }}</dd>
                </div>
            @endforeach
        </dl>

        <section class="flex flex-col gap-3.5 border-t border-line pt-5.25">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                <h4 class="m-0 flex-[1_1_12rem] font-heading text-base font-medium">Datos de SIGLA 1.0</h4>
                <x-status-badge variant="info">Solo lectura · SIGLA 1.0</x-status-badge>
            </div>

            <p wire:loading.flex wire:target="lookUp" role="status" class="m-0 items-center gap-2 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
                <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                Consultando SIGLA 1.0…
            </p>

            <div wire:loading.remove wire:target="lookUp">
                @if ($status === Sigla1LookupStatus::Found)
                    <dl class="m-0 grid grid-cols-2 gap-4 md:grid-cols-4">
                        @foreach ($fromSigla1 as $label => $detail)
                            <div wire:key="sigla1-{{ $loop->index }}" class="flex min-w-0 flex-col gap-0.5">
                                <dt class="text-xs tracking-wider text-ink-muted uppercase">{{ $label }}</dt>
                                <dd class="m-0 break-words">{{ $detail }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <div role="status" class="flex flex-col items-start gap-2.5 rounded-control bg-badge-danger p-3.5 text-sm text-badge-danger-ink">
                        <span class="flex items-start gap-2">
                            <i class="fa-solid fa-circle-xmark mt-0.5" aria-hidden="true"></i>
                            @if ($status === Sigla1LookupStatus::NotFound)
                                SIGLA 1.0 ya no devuelve esta solicitud: no se pueden mostrar el cliente, el producto ni los datos del conductor. Lo guardado en SIGLA 2.0 sí se ve.
                            @else
                                SIGLA 1.0 no responde: no se pueden mostrar el cliente, el producto ni los datos del conductor. Lo guardado en SIGLA 2.0 sí se ve.
                            @endif
                        </span>
                        <flux:button wire:click="lookUp" wire:loading.attr="disabled" wire:target="lookUp" class="max-md:h-13 max-md:w-full">
                            <i class="fa-solid fa-rotate-right text-sm" aria-hidden="true"></i>
                            Reintentar
                        </flux:button>
                    </div>
                @endif
            </div>
        </section>

        <section class="flex flex-col gap-3.5 border-t border-line pt-5.25">
            <h4 class="m-0 font-heading text-base font-medium">Kit</h4>
            <dl class="m-0 grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach ($kit as $label => $detail)
                    <div wire:key="kit-{{ $loop->index }}" class="flex min-w-0 flex-col gap-0.5">
                        <dt class="text-xs tracking-wider text-ink-muted uppercase">{{ $label }}</dt>
                        <dd class="m-0 break-words">{{ $detail }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    </flux:card>

    <section class="flex flex-col items-center gap-1.5 rounded-card border-2 border-dashed border-control px-4 py-6 text-center text-ink-muted">
        <strong class="font-heading text-lg font-medium text-ink">Siguiente paso: las secciones con fotos</strong>
        <span class="max-w-140 text-sm">Esta parte es H18, que sigue en revisión. Hasta entonces, la certificación queda iniciada y se vuelve a abrir buscando su solicitud en Inspección.</span>
    </section>

    <div class="flex justify-end">
        <flux:button :href="route('intranet.certifications.create')" wire:navigate class="max-md:h-13 max-md:w-full">Iniciar otra certificación</flux:button>
    </div>
</div>
