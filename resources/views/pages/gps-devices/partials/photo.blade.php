@if ($gpsDevice->thumbnail_path !== null)
    @php($version = basename((string) $gpsDevice->photo_path, '.jpg'))
    <a href="{{ route('intranet.gps-devices.photo', ['gpsDevice' => $gpsDevice->id, 'v' => $version]) }}" target="_blank" rel="noopener" title="Ver la foto de {{ $gpsDevice->code }}" aria-label="Ver la foto de {{ $gpsDevice->code }}" class="inline-flex size-11 overflow-hidden rounded-control">
        <img src="{{ route('intranet.gps-devices.photo.thumbnail', ['gpsDevice' => $gpsDevice->id, 'v' => $version]) }}" alt="" loading="lazy" class="size-full object-cover">
    </a>
@else
    <span class="text-ink-muted">—</span>
@endif
