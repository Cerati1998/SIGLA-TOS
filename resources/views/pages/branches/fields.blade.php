@props([
    'idPrefix',
])

<x-wire-input id="{{ $idPrefix }}-name" wire:model="form.name" label="Nombre" type="text" required autofocus autocomplete="off" />
<x-wire-input id="{{ $idPrefix }}-ubigeo" wire:model="form.ubigeo" label="Ubigeo" type="text" inputmode="numeric" maxlength="6" required autocomplete="off" />
<x-wire-input id="{{ $idPrefix }}-address" wire:model="form.address" label="Dirección" type="text" required autocomplete="off" />
<x-wire-input id="{{ $idPrefix }}-phone" wire:model="form.phone" label="Teléfono (opcional)" type="tel" autocomplete="off" />
<x-wire-input id="{{ $idPrefix }}-email" wire:model="form.email" label="Correo electrónico (opcional)" type="email" autocomplete="off" />
<x-wire-input id="{{ $idPrefix }}-website" wire:model="form.website" label="Sitio web (opcional)" type="text" autocomplete="off" />
