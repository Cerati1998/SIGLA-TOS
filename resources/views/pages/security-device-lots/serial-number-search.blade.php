<flux:card class="flex flex-col gap-5.25 p-5.25!">
    <h3 class="m-0 font-heading text-h3 font-normal">Buscar un dispositivo</h3>

    <form wire:submit="search" class="flex max-w-105 flex-col gap-1.75">
        <flux:label for="serial-number-search">Serie</flux:label>

        <div class="flex">
            <flux:input
                id="serial-number-search"
                wire:model="serialNumber"
                type="text"
                maxlength="50"
                autocomplete="off"
                class:input="rounded-e-none uppercase"
            />
            <flux:button type="submit" variant="primary" class="rounded-s-none" title="Buscar" aria-label="Buscar" wire:loading.attr="disabled" wire:target="search">
                <x-feather name="search" class="size-4.5" />
            </flux:button>
        </div>

        <span class="text-sm text-ink-muted">Escríbela o escanéala: al escanear busca sola.</span>
        <flux:error name="serialNumber" />
    </form>

    @if ($device !== null)
        <dl class="m-0 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="flex flex-col gap-0.5">
                <dt class="text-xs tracking-wider text-ink-muted uppercase">Serie</dt>
                <dd class="m-0"><strong>{{ $device->serial_number }}</strong></dd>
            </div>
            <div class="flex flex-col gap-0.5">
                <dt class="text-xs tracking-wider text-ink-muted uppercase">Estado</dt>
                <dd class="m-0"><x-status-badge :variant="$device->status->badgeVariant()">{{ $device->status->label() }}</x-status-badge></dd>
            </div>
            <div class="flex flex-col gap-0.5">
                <dt class="text-xs tracking-wider text-ink-muted uppercase">Lote</dt>
                <dd class="m-0">
                    <a href="{{ route('intranet.security-device-lots.show', $device->lot) }}" wire:navigate class="text-brand hover:text-highlight">{{ $device->lot->announcement->lotName() }}</a>
                </dd>
            </div>
            <div class="flex flex-col gap-0.5">
                <dt class="text-xs tracking-wider text-ink-muted uppercase">Kit</dt>
                <dd class="m-0">—</dd>
            </div>
        </dl>
    @elseif ($searchedSerialNumber !== null)
        <p role="status" class="m-0 flex gap-2.5 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
            <x-feather name="alert-triangle" class="mt-0.5 size-4.5" />
            <span>No encontramos un dispositivo con la serie {{ $searchedSerialNumber }}.</span>
        </p>
    @endif
</flux:card>
