@if ($gpsDevice->trashed())
    <x-status-badge variant="warning">Desactivado</x-status-badge>
@else
    <x-status-badge variant="success">{{ $gpsDevice->status->label() }}</x-status-badge>
@endif
