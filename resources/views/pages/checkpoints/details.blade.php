<x-layouts::app :title="'Checkpoint '.$checkpoint->certification->container" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Checkpoints', 'route' => route('intranet.checkpoints.index')],
    ['name' => $checkpoint->certification->container],
]">
    <livewire:checkpoints.record :checkpoint="$checkpoint" />
</x-layouts::app>
