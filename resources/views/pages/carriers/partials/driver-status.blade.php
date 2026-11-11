@if ($driver->link_deleted_at)
    <x-status-badge variant="warning">Desactivado</x-status-badge>
@else
    <x-status-badge variant="success">Activo</x-status-badge>
@endif
