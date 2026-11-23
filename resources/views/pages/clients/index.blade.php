<x-layouts::app title="Clientes" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Clientes'],
]">
    <x-panel.list-card>
        <livewire:tables.clients-table />
    </x-panel.list-card>

    <livewire:clients.create-modal />
    <livewire:clients.edit-modal />
</x-layouts::app>
