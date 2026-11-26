@if ($step->devices->isEmpty())
    <span class="text-sm text-ink-muted">Solo foto</span>
@else
    <div class="flex flex-wrap gap-1.5">
        @foreach ($step->devices as $device)
            <span wire:key="device-{{ $device->id }}" @class([
                'rounded-badge px-2 py-0.5 font-mono text-sm',
                'bg-badge-success text-badge-success-ink ring-1 ring-status-green ring-inset' => in_array($device->id, $installedIds ?? [], true),
                'bg-table-head text-ink' => ! in_array($device->id, $installedIds ?? [], true),
            ]) title="{{ $device->lot->securityDeviceType->name }}">{{ $device->serial_number }}</span>
        @endforeach
    </div>
@endif
