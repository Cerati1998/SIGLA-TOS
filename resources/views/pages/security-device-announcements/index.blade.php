<x-layouts::app title="Anuncios de ingreso de lotes" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Anuncios de ingreso de lotes'],
]">
    <livewire:security-devices.serial-number-search />

    <x-panel.list-card>
        <livewire:tables.security-device-announcements-table />
    </x-panel.list-card>
</x-layouts::app>
