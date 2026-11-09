@if ($provider->trashed())
    <x-status-badge variant="warning">Inactivo</x-status-badge>
@else
    <x-status-badge variant="success">Activo</x-status-badge>
@endif
