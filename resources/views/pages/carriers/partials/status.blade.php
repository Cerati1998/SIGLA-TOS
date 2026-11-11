@if ($carrier->trashed())
    <x-status-badge variant="warning">Desactivado</x-status-badge>
@else
    <x-status-badge variant="success">Activo</x-status-badge>
@endif
