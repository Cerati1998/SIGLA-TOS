<x-layouts::app title="Transportistas" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Transportistas'],
]">
    <x-panel.list-card>
        <livewire:tables.carriers-table />
    </x-panel.list-card>

    <livewire:carriers.create-modal />
    <livewire:carriers.edit-modal />
    <livewire:carriers.drivers-modal />
    <livewire:drivers.edit-modal />
</x-layouts::app>
