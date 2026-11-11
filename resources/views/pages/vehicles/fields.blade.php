@props([
    'idPrefix',
    'kinds',
    'bodies',
    'categories',
])

<div class="grid gap-4 md:grid-cols-2">
    <x-wire-input id="{{ $idPrefix }}-plate" wire:model="form.plate" label="Placa" description="Se guarda en mayúsculas, sin espacios ni guiones." type="text" maxlength="12" required autocomplete="off" />
    <x-wire-input id="{{ $idPrefix }}-brand" wire:model="form.brand" label="Marca" type="text" maxlength="100" required autocomplete="off" />
</div>

<div class="grid gap-4 md:grid-cols-2">
    <x-wire-native-select id="{{ $idPrefix }}-kind" wire:model="form.kind" label="Tipo" :options="$kinds" option-key-value required />
    <x-wire-native-select id="{{ $idPrefix }}-body" wire:model="form.vehicleBodyId" label="Carrocería" description="Solo las carrocerías activas." :options="['' => 'Elige una carrocería'] + $bodies->all()" option-key-value required />
</div>

<div class="md:max-w-50">
    <x-wire-native-select id="{{ $idPrefix }}-category" wire:model="form.category" label="Categoría" :options="$categories" option-key-value required />
</div>
