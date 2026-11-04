@if ($branch->trashed())
    <x-status-badge variant="warning">Inactiva</x-status-badge>
@else
    <x-status-badge variant="success">Activa</x-status-badge>
@endif
