<x-layouts::app title="Tipos de dispositivo" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Tipos de dispositivo'],
]">
    <x-panel.list-card>
        <livewire:tables.security-device-types-table />
    </x-panel.list-card>

    <livewire:security-device-types.create-modal />
    <livewire:security-device-types.edit-modal />
</x-layouts::app>
