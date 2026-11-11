<x-layouts::app title="Vehículos" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Vehículos'],
]">
    <x-panel.list-card>
        <livewire:tables.vehicles-table />
    </x-panel.list-card>

    <livewire:vehicles.create-modal />
    <livewire:vehicles.edit-modal />
</x-layouts::app>
