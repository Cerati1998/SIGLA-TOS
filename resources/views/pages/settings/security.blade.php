<x-layouts::app title="Seguridad" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Seguridad'],
]">
    <livewire:settings.security />
</x-layouts::app>
