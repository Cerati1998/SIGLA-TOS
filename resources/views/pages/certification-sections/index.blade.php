<x-layouts::app title="Secciones de certificación" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Secciones de certificación'],
]">
    <x-panel.list-card>
        <livewire:tables.certification-sections-table />
    </x-panel.list-card>

    <livewire:certification-sections.create-modal />
    <livewire:certification-sections.edit-modal />
    <livewire:certification-sections.order-modal />
</x-layouts::app>
