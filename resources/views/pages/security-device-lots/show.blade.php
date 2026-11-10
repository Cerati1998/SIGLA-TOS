@php
    $announcement = $lot->announcement;
    $details = [
        'Tipo de dispositivo' => $announcement->securityDeviceType->name,
        'Línea naviera' => $announcement->shippingLine->code.' · '.$announcement->shippingLine->name,
        'Proveedor' => $announcement->provider->business_name,
        'Guía' => $lot->dispatch_number,
        'Rango' => $announcement->serialNumber($announcement->series_start).' – '.$announcement->serialNumber($announcement->series_end),
        'Cantidad' => number_format($lot->quantity, 0, ',', ' '),
        'Recepción' => $lot->received_at->format('d/m/Y'),
        'Ingresado por' => $lot->creator->name.' · '.$lot->created_at?->timezone('America/Lima')->format('d/m/Y H:i'),
    ];
    $countStyles = [
        'available' => 'bg-badge-success text-badge-success-ink',
        'in_kit' => 'bg-badge-info text-badge-info-ink',
        'used' => 'border border-line bg-badge-neutral text-badge-neutral-ink',
        'damaged' => 'bg-badge-warning text-badge-warning-ink',
    ];
@endphp

<x-layouts::app :title="'Lote '.$announcement->lotName()" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Lotes de dispositivos', 'route' => route('intranet.security-device-lots.index')],
    ['name' => $announcement->lotName()],
]">
    <x-slot:actions>
        <flux:button
            variant="primary"
            :href="route('intranet.security-device-lots.edit', $lot)"
            wire:navigate
            class="[--color-accent:var(--color-status-green)] max-md:h-11 max-md:w-full"
        >
            <i class="fa-solid fa-pen text-sm" aria-hidden="true"></i>
            Editar
        </flux:button>
    </x-slot:actions>

    <flux:card class="flex flex-col gap-5.25 p-5.25!">
        <dl class="m-0 grid grid-cols-2 gap-4 md:grid-cols-4">
            @foreach ($details as $label => $value)
                <div class="flex min-w-0 flex-col gap-0.5">
                    <dt class="text-xs tracking-wider text-ink-muted uppercase">{{ $label }}</dt>
                    <dd class="m-0 break-words">{{ $value }}</dd>
                </div>
            @endforeach

            <div class="col-span-full flex flex-col gap-0.5">
                <dt class="text-xs tracking-wider text-ink-muted uppercase">Observaciones</dt>
                <dd class="m-0 whitespace-pre-line">{{ $lot->notes ?? 'Sin observaciones.' }}</dd>
            </div>
        </dl>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            @foreach (\App\Enums\SecurityDeviceStatus::cases() as $status)
                <div class="flex flex-col gap-1 rounded-card px-4 py-3.5 {{ $countStyles[$status->value] }}">
                    <strong class="font-heading text-[1.75rem] leading-none font-medium">{{ number_format($deviceCounts[$status->value], 0, ',', ' ') }}</strong>
                    <span class="text-sm">{{ $status->pluralLabel() }}</span>
                </div>
            @endforeach
        </div>
    </flux:card>

    <x-panel.list-card>
        <h3 class="m-0 border-b border-line p-5.25 font-heading text-h3 font-normal">Dispositivos del lote</h3>

        <livewire:tables.security-devices-table :lot-id="$lot->id" />
    </x-panel.list-card>
</x-layouts::app>
