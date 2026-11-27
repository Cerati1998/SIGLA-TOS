{{ $gpsDevice->ownership->label() }}@if ($gpsDevice->rentalProvider !== null)<span class="text-ink-muted"> · {{ $gpsDevice->rentalProvider->business_name }}</span>@endif
