<x-layouts::app :title="'Editar lote '.$lot->name()" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Anuncios de ingreso de lotes', 'route' => route('intranet.security-device-announcements.index')],
    ['name' => $lot->announcement->number(), 'route' => route('intranet.security-device-announcements.show', $lot->announcement)],
    ['name' => $lot->name(), 'route' => route('intranet.security-device-lots.show', $lot)],
    ['name' => 'Editar'],
]">
    <livewire:security-device-lots.edit-form :lot="$lot" />
</x-layouts::app>
