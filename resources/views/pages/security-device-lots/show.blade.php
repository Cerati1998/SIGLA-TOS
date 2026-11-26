@php
    $announcement = $lot->announcement;
    $details = [
        'Tipo de dispositivo' => $lot->securityDeviceType->name,
        'Línea naviera' => $announcement->shippingLine->code.' · '.$announcement->shippingLine->name,
        'Proveedor' => $announcement->provider->business_name,
        'Guía' => $announcement->dispatch_number,
        'Rango' => $lot->serialNumber($lot->series_start).' – '.$lot->serialNumber($lot->series_end),
        'Cantidad' => number_format($lot->quantity, 0, ',', ' '),
        'Recepción' => $announcement->received_at->format('d/m/Y'),
        'Ingresado por' => $announcement->creator->name.' · '.$announcement->created_at?->timezone('America/Lima')->format('d/m/Y H:i'),
    ];
    $countStyles = [
        'inactive' => 'border border-line bg-badge-neutral text-ink-muted',
        'available' => 'bg-badge-success text-badge-success-ink',
        'in_kit' => 'bg-badge-info text-badge-info-ink',
        'used' => 'border border-line bg-badge-neutral text-badge-neutral-ink',
        'damaged' => 'bg-badge-warning text-badge-warning-ink',
        'dispatched' => 'bg-badge-purple text-badge-purple-ink',
        'discarded' => 'bg-badge-danger text-badge-danger-ink',
    ];
@endphp

<x-layouts::app :title="'Lote '.$lot->name()" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Anuncios de ingreso de lotes', 'route' => route('intranet.security-device-announcements.index')],
    ['name' => $announcement->number(), 'route' => route('intranet.security-device-announcements.show', $announcement)],
    ['name' => $lot->name()],
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
            <div class="flex min-w-0 flex-col gap-0.5">
                <dt class="text-xs tracking-wider text-ink-muted uppercase">Anuncio de ingreso de lotes</dt>
                <dd class="m-0"><a href="{{ route('intranet.security-device-announcements.show', $announcement) }}" wire:navigate class="text-brand underline-offset-2 hover:text-accent hover:underline">{{ $announcement->number() }}</a></dd>
            </div>

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

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-7">
            @foreach (\App\Enums\SecurityDeviceStatus::cases() as $status)
                <div class="flex flex-col gap-1 rounded-card px-4 py-3.5 {{ $countStyles[$status->value] }}">
                    <strong class="font-heading text-[1.75rem] leading-none font-medium">{{ number_format($deviceCounts[$status->value], 0, ',', ' ') }}</strong>
                    <span class="text-sm">{{ $status->pluralLabel() }}</span>
                </div>
            @endforeach
        </div>
    </flux:card>

    <x-panel.list-card>
        <div class="flex flex-wrap items-center gap-2 border-b border-line p-5.25">
            <h3 class="m-0 flex-[1_1_12.5rem] font-heading text-h3 font-normal">Dispositivos del lote</h3>

            @if ($lot->requiresApproval())
                <flux:modal.trigger name="lot-approvals">
                    <flux:button class="max-md:h-11 max-md:w-full">
                        <i class="fa-solid fa-clock-rotate-left text-sm" aria-hidden="true"></i>
                        Historial de aprobaciones
                    </flux:button>
                </flux:modal.trigger>

                <flux:button variant="primary" x-on:click="$openModal('security-device-lot-approve')" class="max-md:h-11 max-md:w-full">
                    <i class="fa-solid fa-shield-halved text-sm" aria-hidden="true"></i>
                    Registrar aprobación
                </flux:button>
            @endif
        </div>

        <livewire:tables.security-devices-table :lot-id="$lot->id" />
    </x-panel.list-card>

    <livewire:security-devices.discard-modal :lot="$lot" />
    <livewire:security-devices.discard-detail-modal :lot="$lot" />

    @if ($lot->requiresApproval())
        <livewire:security-device-lots.approve-modal :lot="$lot" />

        <x-pages::security-device-lots.approvals-flyout :lot="$lot" :approved="$lot->quantity - $deviceCounts['inactive']" />
    @endif

</x-layouts::app>
