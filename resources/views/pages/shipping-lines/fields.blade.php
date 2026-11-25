@props([
    'idPrefix',
])

<x-wire-input id="{{ $idPrefix }}-code" wire:model="form.code" label="Código" type="text" maxlength="20" required autofocus autocomplete="off" />
<x-wire-input id="{{ $idPrefix }}-name" wire:model="form.name" label="Nombre" type="text" maxlength="100" required autocomplete="off" />

<flux:radio.group wire:model="form.dispatchMode" label="Modo de despacho" variant="cards" class="flex-col gap-2">
    @foreach (\App\Enums\DispatchMode::cases() as $dispatchMode)
        <flux:radio
            id="{{ $idPrefix }}-dispatch-mode-{{ $dispatchMode->value }}"
            value="{{ $dispatchMode->value }}"
            label="{{ $dispatchMode->label() }}"
            description="{{ $dispatchMode->description() }}"
            indicator="start"
            class="p-3"
        />
    @endforeach
</flux:radio.group>
