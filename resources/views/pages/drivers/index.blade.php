<x-layouts::app title="Conductores" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Conductores'],
]">
    <x-panel.list-card>
        <livewire:tables.drivers-table />
    </x-panel.list-card>

    <livewire:drivers.create-modal />
    <livewire:drivers.edit-modal />
</x-layouts::app>
