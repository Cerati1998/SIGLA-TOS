@php
    $details = [
        'Línea naviera' => $announcement->shippingLine->code.' · '.$announcement->shippingLine->name,
        'Proveedor' => $announcement->provider->business_name,
        'Guía' => $announcement->dispatch_number,
        'Recepción' => $announcement->received_at->format('d/m/Y'),
        'Lotes' => number_format((int) $announcement->lots_count, 0, ',', ' '),
        'Dispositivos' => number_format((int) $announcement->lots_sum_quantity, 0, ',', ' '),
        'Ingresado por' => $announcement->creator->name.' · '.$announcement->created_at?->timezone('America/Lima')->format('d/m/Y H:i'),
    ];
@endphp

<x-layouts::app :title="'Anuncio de ingreso de lotes '.$announcement->number()" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Anuncios de ingreso de lotes', 'route' => route('intranet.security-device-announcements.index')],
    ['name' => $announcement->number()],
]">
    <x-slot:actions>
        <flux:button
            variant="primary"
            :href="route('intranet.security-device-announcements.edit', $announcement)"
            wire:navigate
            class="[--color-accent:var(--color-status-green)] max-md:h-11 max-md:w-full"
        >
            <i class="fa-solid fa-pen text-sm" aria-hidden="true"></i>
            Editar
        </flux:button>

        <flux:button variant="primary" :href="route('intranet.security-device-announcements.lots.create', $announcement)" wire:navigate class="max-md:h-11 max-md:w-full">
            <i class="fa-solid fa-plus text-sm" aria-hidden="true"></i>
            Agregar lotes
        </flux:button>
    </x-slot:actions>

    <flux:card class="p-5.25!">
        <dl class="m-0 grid grid-cols-2 gap-4 md:grid-cols-4">
            @foreach ($details as $label => $value)
                <div @class(['flex min-w-0 flex-col gap-0.5', 'col-span-2' => $label === 'Ingresado por' || $label === 'Proveedor'])>
                    <dt class="text-xs tracking-wider text-ink-muted uppercase">{{ $label }}</dt>
                    <dd class="m-0 break-words">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </flux:card>

    <x-panel.list-card>
        <div class="border-b border-line p-5.25">
            <h3 class="m-0 font-heading text-h3 font-normal">Lotes del anuncio</h3>
        </div>

        <livewire:tables.security-device-lots-table :announcement-id="$announcement->id" />
    </x-panel.list-card>
</x-layouts::app>
