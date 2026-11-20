<x-layouts::app :title="'Inspección '.mb_strtolower($type->label())" :breadcrumbs="[
    ['name' => 'Certificaciones', 'route' => route('intranet.certifications.index')],
    ['name' => 'Inspección '.mb_strtolower($type->label())],
]">
    <livewire:inspections.unit-picker :type="$type" />
</x-layouts::app>
