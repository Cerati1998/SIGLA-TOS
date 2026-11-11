<div>
    <x-wire-modal-card title="Conductores" width="4xl" wire:model="openModal">
        @if ($carrier)
            <p class="m-0 mb-4 text-sm text-ink-muted">{{ $carrier->business_name }} · RUC {{ $carrier->document_number }}</p>

            <livewire:tables.carrier-drivers-table :carrier-id="$carrier->id" :key="'carrier-drivers-'.$carrier->id" />

            <p class="m-0 mt-4 text-sm text-ink-muted">Desactivar o reactivar solo afecta a este transportista. Editar cambia los datos del conductor en todos los transportistas.</p>

            <livewire:drivers.create-modal :carrier-id="$carrier->id" :key="'driver-create-'.$carrier->id" />
        @endif
    </x-wire-modal-card>
</div>
