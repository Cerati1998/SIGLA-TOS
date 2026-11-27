<x-layouts::app title="Modelos GPS" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Modelos GPS'],
]">
    <x-panel.list-card>
        <livewire:tables.gps-device-models-table />
    </x-panel.list-card>

    <livewire:gps-device-models.create-modal />
    <livewire:gps-device-models.edit-modal />

    {{-- After the model windows, so that it opens on top of them from «Agregar». --}}
    <livewire:providers.create-modal />
</x-layouts::app>
