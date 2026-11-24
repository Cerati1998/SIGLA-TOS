{{-- The lots of the announcement as cards on a phone, instead of the table (H37, decided by the user on 2026-11-24). --}}
<ul class="m-0 flex list-none flex-col gap-3 p-5.25 md:hidden">
    @forelse ($this->cardLots() as $lot)
        @php
            $approved = (int) $lot->getAttribute('approved_devices_count');
            [$approvalLabel, $approvalVariant] = match (true) {
                ! $lot->requiresApproval() => ['No requiere', 'success'],
                $approved === 0 => ['0 de '.number_format($lot->quantity, 0, ',', ' '), 'muted'],
                $approved >= $lot->quantity => [number_format($approved, 0, ',', ' ').' de '.number_format($lot->quantity, 0, ',', ' '), 'success'],
                default => [number_format($approved, 0, ',', ' ').' de '.number_format($lot->quantity, 0, ',', ' '), 'warning'],
            };
        @endphp

        <li wire:key="lot-card-{{ $lot->id }}" class="flex flex-col gap-2.5 rounded-card border border-line p-3">
            <div class="flex items-start justify-between gap-2">
                <strong class="text-[0.9375rem]">{{ $lot->securityDeviceType->name }}</strong>
                <x-status-badge :variant="$approvalVariant">{{ $approvalLabel }}</x-status-badge>
            </div>
            <span class="text-sm">{{ $lot->serialNumber($lot->series_start) }} – {{ $lot->serialNumber($lot->series_end) }} · {{ number_format($lot->quantity, 0, ',', ' ') }}</span>
            @if ($lot->notes !== null)
                <span class="text-sm text-ink-muted">{{ $lot->notes }}</span>
            @endif

            @include('pages.security-device-lots.partials.actions', ['lot' => $lot, 'canRemove' => $this->canRemove($lot)])
        </li>
    @empty
        <li class="text-sm text-ink-muted">Este anuncio no tiene lotes.</li>
    @endforelse
</ul>
