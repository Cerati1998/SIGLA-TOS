<x-layouts::app title="Trabajadores" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Trabajadores'],
]">
    <x-panel.list-card>
        <livewire:tables.workers-table />
    </x-panel.list-card>

    <livewire:workers.create-modal />
    <livewire:workers.edit-modal />

    {{-- After the worker windows, so that it opens on top of them from «Agregar». --}}
    <livewire:providers.create-modal />
</x-layouts::app>
