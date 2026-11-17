<x-layouts::app :title="'Kit '.$kit->pouch_code" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Kits', 'route' => route('intranet.kits.index')],
    ['name' => $kit->pouch_code],
]">
    <livewire:kits.device-scanner :kit="$kit" />
</x-layouts::app>
