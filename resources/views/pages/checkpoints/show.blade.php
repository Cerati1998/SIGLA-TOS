<x-layouts::app :title="'Checkpoint · '.$checkpoint->certification->container" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Registrar checkpoint', 'route' => route('intranet.checkpoints.pending')],
    ['name' => $checkpoint->certification->container],
]">
    <livewire:checkpoints.step-form :checkpoint="$checkpoint" />
</x-layouts::app>
