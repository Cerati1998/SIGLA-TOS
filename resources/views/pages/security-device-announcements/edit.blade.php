<x-layouts::app :title="'Editar anuncio de ingreso de lotes '.$announcement->number()" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Anuncios de ingreso de lotes', 'route' => route('intranet.security-device-announcements.index')],
    ['name' => $announcement->number(), 'route' => route('intranet.security-device-announcements.show', $announcement)],
    ['name' => 'Editar'],
]">
    <livewire:security-device-announcements.edit-form :announcement="$announcement" />
</x-layouts::app>
