@props([
    'idPrefix',
])

{{-- A provider created with «Agregar» is chosen here: the provider window tells its id with the provider-created event. --}}
<div x-on:provider-created.window="$wire.form.brandId = $event.detail.providerId" class="grid gap-1.75">
    <x-wire-select
        id="{{ $idPrefix }}-brand"
        wire:model="form.brandId"
        label="Marca"
        placeholder="Busca por razón social o documento"
        :async-data="['api' => route('intranet.providers.options'), 'method' => 'POST']"
        option-label="name"
        option-value="id"
    >
        <x-slot:corner>
            <x-label-link x-on:click="$openModal('provider-create')">+ Agregar</x-label-link>
        </x-slot:corner>
    </x-wire-select>
    <span class="text-sm text-ink-muted">Es un proveedor. Solo proveedores activos.</span>
</div>

<div class="grid gap-1.75">
    <x-wire-input id="{{ $idPrefix }}-name" wire:model="form.name" label="Nombre" type="text" maxlength="100" required autocomplete="off" />
    <span class="text-sm text-ink-muted">No se repite en la misma marca.</span>
</div>

<div class="grid gap-4 md:grid-cols-2">
    <x-wire-native-select id="{{ $idPrefix }}-category" wire:model="form.category" label="Categoría" :options="['' => 'Elige una categoría'] + \App\Enums\GpsDeviceCategory::options()" option-key-value required />

    <div class="grid content-start gap-1.75">
        <x-wire-native-select id="{{ $idPrefix }}-opening-type" wire:model="form.openingType" label="Apertura" :options="['' => 'Elige la apertura'] + \App\Enums\GpsOpeningType::options()" option-key-value required />
        <span class="text-sm text-ink-muted">«Sin apertura» para GPS portátiles y de tolva.</span>
    </div>
</div>

<div class="grid gap-1.75">
    <div class="grid gap-4 md:grid-cols-2">
        <x-wire-input id="{{ $idPrefix }}-monitoring-product-code" wire:model="form.monitoringProductCode" label="Código de producto (opcional)" type="text" maxlength="20" autocapitalize="characters" autocomplete="off" />
        <x-wire-input id="{{ $idPrefix }}-monitoring-device-type-code" wire:model="form.monitoringDeviceTypeCode" label="Código de tipo (opcional)" type="text" maxlength="20" inputmode="numeric" autocomplete="off" />
    </div>
    <span class="text-sm text-ink-muted">Como los reporta la plataforma de monitoreo (por ejemplo, BR y 240). Van los dos o ninguno, y el par no se repite en otro modelo.</span>
</div>

<x-wire-textarea id="{{ $idPrefix }}-description" wire:model="form.description" label="Descripción (opcional)" rows="3" maxlength="255" />
