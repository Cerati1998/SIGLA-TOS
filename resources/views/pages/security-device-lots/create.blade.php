<x-layouts::app title="Ingresar lote" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Lotes de dispositivos', 'route' => route('intranet.security-device-lots.index')],
    ['name' => 'Ingresar lote'],
]">
    <livewire:security-device-lots.create-form />
</x-layouts::app>
