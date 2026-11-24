@if ($step->devices->isEmpty())
    <span class="text-sm text-ink-muted">Solo foto</span>
@else
    <div class="flex flex-wrap gap-1.5">
        @foreach ($step->devices as $device)
            <span wire:key="device-{{ $device->id }}" class="rounded-badge bg-table-head px-2 py-0.5 font-mono text-sm text-ink" title="{{ $device->lot->securityDeviceType->name }}">{{ $device->serial_number }}</span>
        @endforeach
    </div>
@endif
