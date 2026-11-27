<x-layouts::app title="Dispositivos GPS" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Dispositivos GPS'],
]">
    <x-panel.list-card>
        <livewire:tables.gps-devices-table />
    </x-panel.list-card>

    <livewire:gps-devices.create-modal />
    <livewire:gps-devices.edit-modal />

    {{-- After the device windows, so that they open on top of them from «Agregar». --}}
    <livewire:gps-device-models.create-modal />
    <livewire:providers.create-modal />
</x-layouts::app>
