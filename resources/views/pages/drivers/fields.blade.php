@props([
    'idPrefix',
])

<div class="md:max-w-75">
    <x-document-lookup.number
        id="{{ $idPrefix }}-document-number"
        :digits="[\App\Models\Identity::DNI => \App\Livewire\Forms\DriverForm::DNI_DIGITS]"
        :maxlength="\App\Livewire\Forms\DriverForm::DNI_DIGITS"
        label="DNI"
        button-label="Consultar DNI"
    />
</div>

<x-document-lookup.notices noun="DNI" />

{{-- A disabled fieldset disables the input inside it while the DNI is being looked up. --}}
<fieldset wire:loading.attr="disabled" wire:target="lookup" class="m-0 min-w-0 border-0 p-0">
    <x-wire-input id="{{ $idPrefix }}-full-name" wire:model="form.fullName" label="Nombres y apellidos" type="text" maxlength="255" required autocomplete="off" />
</fieldset>

<div class="md:max-w-75">
    <x-wire-input id="{{ $idPrefix }}-license" wire:model="form.license" label="Brevete" description="Hasta 15 caracteres. Se guarda en mayúsculas y sin espacios." type="text" maxlength="15" required autocomplete="off" />
</div>
