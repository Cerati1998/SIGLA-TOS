<x-layouts::app title="Empresa" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Empresa'],
]">
    <livewire:company.edit-form />
</x-layouts::app>
