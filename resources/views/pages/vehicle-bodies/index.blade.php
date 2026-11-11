<x-layouts::app title="Carrocerías" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Carrocerías'],
]">
    <x-panel.list-card>
        <livewire:tables.vehicle-bodies-table />
    </x-panel.list-card>

    <livewire:vehicle-bodies.create-modal />
    <livewire:vehicle-bodies.edit-modal />
</x-layouts::app>
