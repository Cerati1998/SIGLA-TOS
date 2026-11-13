@props([
    'idPrefix',
    'branches',
])

<x-wire-native-select id="{{ $idPrefix }}-branch" wire:model="form.branchId" label="Sucursal" description="Solo sucursales activas de la empresa." :options="['' => 'Elige una sucursal'] + $branches->all()" option-key-value required />

<x-wire-input id="{{ $idPrefix }}-name" wire:model="form.name" label="Nombre" description="No se repite en la misma sucursal. Hasta 100 caracteres." type="text" maxlength="100" required autocomplete="off" />

<x-wire-input id="{{ $idPrefix }}-capacity" wire:model="form.capacity" label="Capacidad (contenedores)" description="Número entero mayor que 0. Es solo informativa: el sistema no controla cuánto hay en la zona." type="text" inputmode="numeric" maxlength="5" required autocomplete="off" />
