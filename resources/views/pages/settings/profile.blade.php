<x-layouts::app title="Perfil" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Perfil'],
]">
    <livewire:settings.profile />
</x-layouts::app>
