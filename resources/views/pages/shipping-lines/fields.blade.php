@props([
    'idPrefix',
])

<x-wire-input id="{{ $idPrefix }}-code" wire:model="form.code" label="Código" type="text" maxlength="20" required autofocus autocomplete="off" />
<x-wire-input id="{{ $idPrefix }}-name" wire:model="form.name" label="Nombre" type="text" maxlength="100" required autocomplete="off" />
