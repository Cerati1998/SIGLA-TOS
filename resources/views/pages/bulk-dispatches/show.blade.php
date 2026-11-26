@php
    $data = [
        'Fecha' => ($bulkDispatch->created_at ?? now())->timezone('America/Lima')->format('d/m/Y H:i'),
        'Semana' => $bulkDispatch->week(),
        'Despachó' => $bulkDispatch->creator->name,
        'Recibió' => $bulkDispatch->receiver->full_name,
        'Motivo de salida' => $bulkDispatch->reason->label(),
        $bulkDispatch->reason === \App\Enums\BulkDispatchReason::Sale ? 'Cliente' : 'Kit' => $bulkDispatch->reason === \App\Enums\BulkDispatchReason::Sale
            ? $bulkDispatch->client?->business_name
            : $bulkDispatch->kit?->pouch_code.' · '.$bulkDispatch->kit?->kitType->name,
        'Patio de destino' => $bulkDispatch->yardZone->name,
        'Dispositivos' => array_sum(array_column($types, 'count')),
    ];
@endphp

<x-layouts::app :title="'Despacho '.$bulkDispatch->number()" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Despachos a granel', 'route' => route('intranet.bulk-dispatches.index')],
    ['name' => $bulkDispatch->number()],
]">
    <div class="flex flex-col gap-5">
        <div class="flex justify-end max-md:flex-col max-md:items-stretch">
            <flux:button :href="route('intranet.bulk-dispatches.pdf', $bulkDispatch)" target="_blank" variant="primary" class="[--color-accent:var(--color-danger)] max-md:h-13">
                <i class="fa-solid fa-file-pdf text-sm" aria-hidden="true"></i>
                Descargar PDF
            </flux:button>
        </div>

        <section class="flex flex-col gap-3.5 rounded-card bg-white p-4 shadow-card">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="m-0 font-heading text-xl font-normal text-brand">{{ $bulkDispatch->number() }}</h3>
                <x-status-badge :variant="$bulkDispatch->reason->badgeVariant()">{{ $bulkDispatch->reason->label() }}</x-status-badge>
            </div>
            <dl class="m-0 grid grid-cols-1 gap-x-5 gap-y-3.5 md:grid-cols-4">
                @foreach ($data as $label => $value)
                    <div class="flex flex-col gap-0.5">
                        <dt class="text-xs tracking-wider text-ink-muted uppercase">{{ $label }}</dt>
                        <dd class="m-0 text-[0.9375rem] font-medium">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
            @if ($bulkDispatch->notes !== null)
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs tracking-wider text-ink-muted uppercase">Observación</span>
                    <p class="m-0 text-[0.9375rem]">{{ $bulkDispatch->notes }}</p>
                </div>
            @endif
        </section>

        @if ($assignment !== null && $assignmentStatus !== null)
            <section class="flex flex-col gap-3.5 rounded-card bg-white p-4 shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="m-0 font-heading text-xl font-normal text-brand">Asignación del kit</h3>
                    <x-status-badge :variant="$assignmentStatus->badgeVariant()">{{ $assignmentStatus->label() }}</x-status-badge>
                </div>
                <dl class="m-0 grid grid-cols-1 gap-x-5 gap-y-3.5 md:grid-cols-4">
                    @foreach ($assignment as $label => $value)
                        <div class="flex flex-col gap-0.5">
                            <dt class="text-xs tracking-wider text-ink-muted uppercase">{{ $label }}</dt>
                            <dd class="m-0 text-[0.9375rem] font-medium">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif

        <h3 class="m-0 font-heading text-xl font-normal text-brand">Detalle de dispositivos</h3>

        @foreach ($types as $type)
            <section class="flex flex-col gap-2.5 rounded-card bg-white p-4 shadow-card">
                <div class="flex flex-wrap justify-between gap-1.5">
                    <h4 class="m-0 font-heading text-base font-medium text-brand">{{ $type['type'] }}</h4>
                    <span class="text-sm text-ink-muted">{{ count($type['ranges']) === 1 ? 'Rango' : 'Rangos' }} {{ implode(', ', $type['ranges']) }} · Cantidad {{ $type['count'] }}</span>
                </div>
                <ul class="m-0 flex list-none flex-wrap gap-1.5 p-0">
                    @foreach ($type['serialNumbers'] as $serialNumber)
                        <li class="rounded-control border border-line bg-white px-2 py-0.75 text-[0.8125rem] tabular-nums">{{ $serialNumber }}</li>
                    @endforeach
                </ul>
            </section>
        @endforeach

        <div>
            <flux:button :href="route('intranet.bulk-dispatches.index')" class="h-11">Volver a la lista</flux:button>
        </div>
    </div>
</x-layouts::app>
