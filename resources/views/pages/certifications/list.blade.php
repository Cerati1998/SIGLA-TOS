<x-layouts::app title="Asignaciones" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Certificaciones', 'route' => route('intranet.certifications.index')],
    ['name' => 'Asignaciones'],
]">
    <x-panel.list-card>
        <livewire:tables.certifications-table />
    </x-panel.list-card>

    <livewire:certifications.reopen-modal />
</x-layouts::app>
