<div>
    {{-- Almost full screen: as wide as the screen allows, and as tall, less the margin and the header of the modal. --}}
    <x-wire-modal-card title="Conductores" width="sm:max-w-none" wire:model="openModal">
        @if ($carrier)
            <div class="flex min-h-[calc(100dvh-9rem)] flex-col gap-4">
                <p class="m-0 text-sm text-ink-muted">{{ $carrier->business_name }} · RUC {{ $carrier->document_number }}</p>

                <div class="flex-1">
                    <livewire:tables.carrier-drivers-table :carrier-id="$carrier->id" :key="'carrier-drivers-'.$carrier->id" />
                </div>

                <p class="m-0 text-sm text-ink-muted">Desactivar o reactivar solo afecta a este transportista. Editar cambia los datos del conductor en todos los transportistas.</p>
            </div>

            <livewire:drivers.create-modal :carrier-id="$carrier->id" :key="'driver-create-'.$carrier->id" />
        @endif
    </x-wire-modal-card>
</div>
