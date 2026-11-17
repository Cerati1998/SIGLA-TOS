<x-layouts::app title="Despachos a granel" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Despachos a granel'],
]">
    <x-panel.list-card>
        <livewire:tables.bulk-dispatches-table />
    </x-panel.list-card>

    <livewire:bulk-dispatches.create-modal />
    <livewire:clients.create-modal />
</x-layouts::app>
