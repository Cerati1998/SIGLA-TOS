<x-layouts::app :title="'Agregar lotes a '.$announcement->number()" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Anuncios de ingreso de lotes', 'route' => route('intranet.security-device-announcements.index')],
    ['name' => $announcement->number(), 'route' => route('intranet.security-device-announcements.show', $announcement)],
    ['name' => 'Agregar lotes'],
]">
    <livewire:security-device-announcements.create-form :announcement="$announcement" />
</x-layouts::app>
