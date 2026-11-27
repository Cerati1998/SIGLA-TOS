@props([
    'idPrefix',
    'branches',
])

<div class="grid gap-4 md:grid-cols-2">
    <x-wire-input id="{{ $idPrefix }}-code" wire:model="form.code" label="Código" description="El que lleva el equipo. Se guarda en mayúsculas." type="text" maxlength="30" autocapitalize="characters" required autocomplete="off" />
    <x-wire-input id="{{ $idPrefix }}-monitoring-asset-id" wire:model="form.monitoringAssetId" label="ID de 24HM (opcional)" description="Solo números. No se repite." type="text" inputmode="numeric" maxlength="12" x-digits-only autocomplete="off" />
</div>

{{-- A model created with «Agregar» is chosen here: the model window tells its id with the gps-device-model-created event. --}}
<div x-on:gps-device-model-created.window="$wire.form.gpsDeviceModelId = $event.detail.gpsDeviceModelId" class="grid gap-1.75">
    <x-wire-select
        id="{{ $idPrefix }}-model"
        wire:model="form.gpsDeviceModelId"
        label="Modelo"
        placeholder="Busca por modelo o marca"
        :async-data="['api' => route('intranet.gps-device-models.options'), 'method' => 'POST']"
        option-label="name"
        option-value="id"
        option-description="description"
    >
        <x-slot:corner>
            <x-label-link x-on:click="$openModal('gps-device-model-create')">+ Agregar</x-label-link>
        </x-slot:corner>
    </x-wire-select>
    <span class="text-sm text-ink-muted">Solo modelos activos.</span>
</div>

<fieldset class="m-0 flex flex-col gap-1.75 border-0 p-0">
    <legend class="mb-1.75 p-0 text-sm font-medium text-ink">Propiedad</legend>
    <div class="grid grid-cols-2 gap-2.5">
        @foreach (\App\Enums\GpsDeviceOwnership::options() as $value => $label)
            <label for="{{ $idPrefix }}-ownership-{{ $value }}" class="flex min-h-11 cursor-pointer items-center gap-2 rounded-control border border-control px-3 py-2 has-checked:border-2 has-checked:border-brand has-checked:bg-badge-info md:min-h-10.25">
                <input id="{{ $idPrefix }}-ownership-{{ $value }}" type="radio" wire:model="form.ownership" value="{{ $value }}" class="size-4 accent-brand">
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>
    <flux:error name="form.ownership" />
</fieldset>

{{-- A provider created with «Agregar» is chosen here: the provider window tells its id with the provider-created event. --}}
<div
    x-show="$wire.form.ownership === '{{ \App\Enums\GpsDeviceOwnership::Rented->value }}'"
    x-cloak
    x-on:provider-created.window="$wire.form.rentalProviderId = $event.detail.providerId"
    class="grid gap-1.75"
>
    <x-wire-select
        id="{{ $idPrefix }}-rental-provider"
        wire:model="form.rentalProviderId"
        label="Proveedor del alquiler"
        placeholder="Busca por razón social o documento"
        :async-data="['api' => route('intranet.providers.options'), 'method' => 'POST']"
        option-label="name"
        option-value="id"
    >
        <x-slot:corner>
            <x-label-link x-on:click="$openModal('provider-create')">+ Agregar</x-label-link>
        </x-slot:corner>
    </x-wire-select>
    <span class="text-sm text-ink-muted">Solo con «Alquilado». Solo proveedores activos.</span>
</div>

<x-wire-native-select id="{{ $idPrefix }}-branch" wire:model="form.branchId" label="Sucursal" description="Solo sucursales activas." :options="['' => 'Elige una sucursal'] + $branches->all()" option-key-value required />

<x-wire-textarea id="{{ $idPrefix }}-notes" wire:model="form.notes" label="Observaciones (opcional)" rows="2" maxlength="500" />

<div class="grid gap-1.75">
    <x-photo-field label="Foto de referencia (opcional)" name="form.photo" title="Foto de referencia" gallery />
    <span class="text-sm text-ink-muted">Una sola foto. Se comprime antes de subirla y lleva el código, la fecha y la hora.</span>
</div>
