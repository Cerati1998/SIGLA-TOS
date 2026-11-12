<x-layouts::app title="Tipos de kit" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Tipos de kit'],
]">
    <x-panel.list-card>
        <livewire:tables.kit-types-table />
    </x-panel.list-card>

    <livewire:kit-types.create-modal />
    <livewire:kit-types.edit-modal />
</x-layouts::app>
