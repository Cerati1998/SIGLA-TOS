@props([
    'idPrefix',
])

<x-wire-input id="{{ $idPrefix }}-name" wire:model="form.name" label="Nombre" type="text" required autofocus autocomplete="off" />
<x-wire-input id="{{ $idPrefix }}-email" wire:model="form.email" label="Correo electrónico" type="email" required autocomplete="off" />
