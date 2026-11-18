<x-layouts::app title="Iniciar certificación" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Iniciar certificación'],
]">
    <livewire:certifications.start-form />
</x-layouts::app>
