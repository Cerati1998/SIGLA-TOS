@if ($worker->is_own)
    <x-status-badge>Propio</x-status-badge>
@else
    {{ $worker->provider?->business_name }}
@endif
