<x-layouts::app :title="'Editar lote '.$lot->announcement->lotName()" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Lotes de dispositivos', 'route' => route('intranet.security-device-lots.index')],
    ['name' => $lot->announcement->lotName(), 'route' => route('intranet.security-device-lots.show', $lot)],
    ['name' => 'Editar'],
]">
    <livewire:security-device-lots.edit-form :lot="$lot" />
</x-layouts::app>
