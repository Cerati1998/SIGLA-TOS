<x-layouts::app title="Líneas navieras" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Líneas navieras'],
]">
    <x-panel.list-card>
        <livewire:tables.shipping-lines-table />
    </x-panel.list-card>

    <livewire:shipping-lines.create-modal />
    <livewire:shipping-lines.edit-modal />
</x-layouts::app>
