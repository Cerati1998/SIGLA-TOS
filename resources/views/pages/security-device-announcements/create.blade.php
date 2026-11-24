<x-layouts::app title="Nuevo anuncio de ingreso de lotes" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Anuncios de ingreso de lotes', 'route' => route('intranet.security-device-announcements.index')],
    ['name' => 'Nuevo'],
]">
    <livewire:security-device-announcements.create-form />
</x-layouts::app>
