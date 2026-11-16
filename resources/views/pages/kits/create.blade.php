<x-layouts::app title="Nuevo kit" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Kits', 'route' => route('intranet.kits.index')],
    ['name' => 'Nuevo kit'],
]">
    <livewire:kits.create-form />
</x-layouts::app>
