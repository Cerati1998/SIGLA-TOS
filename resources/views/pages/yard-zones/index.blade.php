<x-layouts::app title="Zonas de patio" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Zonas de patio'],
]">
    <x-panel.list-card>
        <livewire:tables.yard-zones-table />
    </x-panel.list-card>

    <livewire:yard-zones.create-modal />
    <livewire:yard-zones.edit-modal />
</x-layouts::app>
