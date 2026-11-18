<x-layouts::app :title="'Certificación '.$certification->container" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Inspección', 'route' => route('intranet.certifications.create')],
    ['name' => $certification->container],
]">
    <livewire:certifications.details :certification="$certification" />
</x-layouts::app>
