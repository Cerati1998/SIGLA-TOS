<x-layouts::app title="Usuarios" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Usuarios'],
]">
    <x-panel.list-card>
        <livewire:tables.users-table />
    </x-panel.list-card>

    <livewire:users.form-modal />
</x-layouts::app>
