<x-layouts::app title="Checkpoints" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Checkpoints'],
]">
    <x-panel.list-card>
        <livewire:tables.checkpoints-table />
    </x-panel.list-card>
</x-layouts::app>
