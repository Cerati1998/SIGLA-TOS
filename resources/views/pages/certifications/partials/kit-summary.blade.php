@php
    $dispatch = $kit->dispatch();
    $pouchTypeId = $kit->pouchType()?->id;
    $devicesBySlot = collect($contents['devices'])->groupBy(fn (array $device): string => $device['type'].'|'.$device['assignment']);
@endphp

<section x-data="{ showContents: true }" class="flex flex-col gap-3.5 rounded-card border border-line p-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <strong class="font-heading text-xl font-medium text-brand">{{ $kit->pouch_code }}</strong>
        <x-status-badge :variant="$kit->status->badgeVariant()">{{ $kit->status->label() }}</x-status-badge>
    </div>

    <dl class="m-0 grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="col-span-2 flex min-w-0 flex-col gap-0.5 md:col-span-1">
            <dt class="text-xs tracking-wider text-ink-muted uppercase">Tipo de kit</dt>
            <dd class="m-0 break-words">{{ $kit->kitType->name }}</dd>
        </div>
        <div class="col-span-2 flex min-w-0 flex-col gap-0.5 md:col-span-1">
            <dt class="text-xs tracking-wider text-ink-muted uppercase">Despachado a</dt>
            <dd class="m-0 break-words">{{ $dispatch?->receiver->full_name ?? '—' }}</dd>
        </div>
        <div class="flex min-w-0 flex-col gap-0.5">
            <dt class="text-xs tracking-wider text-ink-muted uppercase">Naviera del kit</dt>
            <dd class="m-0 break-words">{{ $kit->shippingLine->code }} · {{ $kit->shippingLine->name }}</dd>
        </div>
        <div class="flex min-w-0 flex-col gap-0.5">
            <dt class="text-xs tracking-wider text-ink-muted uppercase">Dispositivos</dt>
            <dd class="m-0">{{ count($contents['devices']) }}</dd>
        </div>
    </dl>

    <div>
        <button type="button" x-on:click="showContents = ! showContents" x-bind:aria-expanded="showContents" class="flex min-h-12 w-full items-center justify-between border-t border-line pt-1 text-base font-medium text-brand">
            <span x-text="showContents ? 'Ocultar el contenido' : 'Ver el contenido'"></span>
            <i class="fa-solid fa-chevron-down text-sm transition-transform" x-bind:class="showContents && 'rotate-180'" aria-hidden="true"></i>
        </button>

        <div x-show="showContents" class="flex flex-col">
            @if ($pouchTypeId !== null)
                <p class="m-0 mb-2.5 flex items-start gap-2 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
                    <x-feather name="info" class="mt-0.5 size-4 shrink-0" />
                    <span><strong>Funda {{ $kit->pouch_code }}</strong> · es el depósito del kit: no se reparte en las secciones ni se usa en la inspección.</span>
                </p>
            @endif

            @foreach ($contents['slots'] as $slot)
                @continue($slot['securityDeviceTypeId'] === $pouchTypeId)

                @php
                    $assignment = $slot['assignment']?->label() ?? '-';
                    $devices = $devicesBySlot->get($slot['name'].'|'.$assignment, collect());
                @endphp
                <div wire:key="kit-slot-{{ $slot['key'] }}" class="flex flex-col gap-2 border-t border-surface py-2.5 md:grid md:grid-cols-[14rem_minmax(0,1fr)] md:gap-3">
                    <div class="flex flex-wrap items-center gap-2 text-sm font-medium">
                        {{ $slot['name'] }}
                        @if ($slot['assignment'] !== null)
                            <x-status-badge :variant="$slot['assignment'] === \App\Enums\KitAssignment::External ? 'info' : 'purple'">{{ $assignment }}</x-status-badge>
                        @endif
                        <span class="ms-auto text-brand md:ms-0 md:font-normal md:text-ink-muted">{{ $devices->count() }}</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($devices as $device)
                            <span wire:key="kit-device-{{ $device['serialNumber'] }}" class="inline-flex h-7 items-center rounded-full border border-line bg-surface px-2.5 text-sm">{{ $device['serialNumber'] }}</span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
