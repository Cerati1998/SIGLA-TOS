<x-layouts::app :title="'Certificación '.$certification->container" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Certificaciones', 'route' => route('intranet.certifications.index')],
    ['name' => 'Asignaciones', 'route' => route('intranet.certifications.list')],
    ['name' => $certification->container],
]">
    <livewire:certifications.details :certification="$certification" />
</x-layouts::app>
