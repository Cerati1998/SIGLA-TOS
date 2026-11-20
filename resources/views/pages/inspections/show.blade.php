<x-layouts::app :title="'Inspección '.mb_strtolower($type->label()).' · '.$certification->container" :breadcrumbs="[
    ['name' => 'Certificaciones', 'route' => route('intranet.certifications.index')],
    ['name' => 'Inspección '.mb_strtolower($type->label()), 'route' => route('intranet.inspections.index', $type)],
    ['name' => $certification->container],
]">
    <livewire:inspections.step-form :certification="$certification" :type="$type" />
</x-layouts::app>
