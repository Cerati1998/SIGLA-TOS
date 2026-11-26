<x-layouts::app :title="'Checkpoint · '.$checkpoint->certification->container" :breadcrumbs="[
    ['name' => 'Registrar checkpoint', 'route' => route('intranet.checkpoints.pending')],
    ['name' => $checkpoint->certification->container],
]">
    <livewire:checkpoints.step-form :checkpoint="$checkpoint" />
</x-layouts::app>
