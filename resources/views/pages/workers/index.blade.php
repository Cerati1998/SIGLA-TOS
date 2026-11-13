<x-layouts::app title="Trabajadores" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Trabajadores'],
]">
    <x-panel.list-card>
        <livewire:tables.workers-table />
    </x-panel.list-card>

    <livewire:workers.create-modal />
    <livewire:workers.edit-modal />
</x-layouts::app>
