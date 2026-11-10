<x-layouts::app title="Lotes de dispositivos" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Lotes de dispositivos'],
]">
    <livewire:security-devices.serial-number-search />

    <x-panel.list-card>
        <livewire:tables.security-device-lots-table />
    </x-panel.list-card>
</x-layouts::app>
