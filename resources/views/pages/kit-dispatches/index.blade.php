<x-layouts::app title="Despachos" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Despachos'],
]">
    <x-panel.list-card>
        <livewire:tables.kit-dispatches-table />
    </x-panel.list-card>

    <livewire:kit-dispatches.create-modal />
</x-layouts::app>
