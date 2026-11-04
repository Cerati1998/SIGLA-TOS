@props([
    'idPrefix',
    'identities',
    'branches',
])

<x-wire-input id="{{ $idPrefix }}-name" wire:model="form.name" label="Nombre" type="text" required autofocus autocomplete="off" />
<x-wire-input id="{{ $idPrefix }}-email" wire:model="form.email" label="Correo electrónico" type="email" required autocomplete="off" />
<x-wire-native-select id="{{ $idPrefix }}-identity" wire:model="form.identityId" label="Tipo de documento" placeholder="Selecciona" :options="$identities->pluck('description', 'id')" option-key-value required />
<x-wire-input id="{{ $idPrefix }}-document-number" wire:model="form.documentNumber" label="Número de documento" type="text" maxlength="15" required autocomplete="off" />
<x-wire-native-select id="{{ $idPrefix }}-branch" wire:model="form.branchId" label="Sucursal" placeholder="Selecciona" :options="$branches" option-value="id" option-label="name" required />
