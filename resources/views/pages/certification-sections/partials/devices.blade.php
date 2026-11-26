@if ($certificationSection->verifiedSections->isNotEmpty())
    <span class="inline-flex max-w-xs items-start gap-1.5 text-[13px] whitespace-normal text-ink">
        <i class="fa-solid fa-check mt-0.5 text-brand" aria-hidden="true"></i>
        <span><span class="font-semibold text-brand">Verifica:</span> {{ $certificationSection->verifiedSections->map(fn ($verified) => $verified->name.($verified->trashed() ? ' (desactivada)' : ''))->join(', ') }}</span>
    </span>
@elseif ($certificationSection->securityDeviceTypes->isEmpty())
    <span class="inline-flex items-center gap-1.5 text-[13px] text-ink-muted">
        <x-feather name="camera" class="size-4" />
        Solo foto
    </span>
@else
    <ul class="flex max-w-xs flex-wrap gap-1 whitespace-normal" aria-label="Lo que lleva {{ $certificationSection->name }}">
        @foreach ($certificationSection->securityDeviceTypes as $deviceType)
            <li @class([
                'inline-flex items-center gap-1 rounded-control px-2 py-0.5 text-[13px]',
                'bg-table-head text-ink' => ! $deviceType->trashed(),
                'bg-badge-warning text-badge-warning-ink' => $deviceType->trashed(),
            ])>
                <span class="font-heading font-medium text-brand">{{ $deviceType->pivot->quantity }}</span>
                {{ $certificationSection->deviceTypeName($deviceType) }}{{ $deviceType->trashed() ? ' (desactivado)' : '' }}
            </li>
        @endforeach
    </ul>
@endif
