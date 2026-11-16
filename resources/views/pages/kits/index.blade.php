<x-layouts::app title="Kits" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Kits'],
]">
    <x-panel.list-card>
        <livewire:tables.kits-table />
    </x-panel.list-card>
</x-layouts::app>
