@if ($certificationSection->trashed())
    <x-status-badge variant="warning">Desactivada</x-status-badge>
@else
    <x-status-badge variant="success">Activa</x-status-badge>
@endif
