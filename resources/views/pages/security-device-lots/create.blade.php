<x-layouts::app title="Ingresar lote" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Ingresar lote'],
]">
    <livewire:security-device-lots.create-form />
</x-layouts::app>
