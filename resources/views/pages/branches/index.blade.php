<x-layouts::app title="Sucursales" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Sucursales'],
]">
    <x-panel.list-card>
        <livewire:tables.branches-table />
    </x-panel.list-card>

    <livewire:branches.create-modal />
    <livewire:branches.edit-modal />
</x-layouts::app>
