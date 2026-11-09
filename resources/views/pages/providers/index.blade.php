<x-layouts::app title="Proveedores" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Proveedores'],
]">
    <x-panel.list-card>
        <livewire:tables.providers-table />
    </x-panel.list-card>

    <livewire:providers.create-modal />
    <livewire:providers.edit-modal />
</x-layouts::app>
