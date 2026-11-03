<x-layouts::app title="Usuarios" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Usuarios'],
]">
    <x-panel.list-card>
        <livewire:tables.users-table />
    </x-panel.list-card>

    <livewire:users.create-modal />
    <livewire:users.edit-modal />
</x-layouts::app>
