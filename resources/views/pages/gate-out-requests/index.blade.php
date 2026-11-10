<x-layouts::app title="Consultar solicitud de retiro" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Solicitudes de retiro'],
]">
    <livewire:gate-out-requests.search />
</x-layouts::app>
