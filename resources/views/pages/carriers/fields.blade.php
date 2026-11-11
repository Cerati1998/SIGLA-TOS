@props([
    'idPrefix',
])

<div class="md:max-w-75">
    <x-document-lookup.number
        id="{{ $idPrefix }}-document-number"
        :digits="[\App\Models\Identity::RUC => \App\Livewire\Forms\CarrierForm::RUC_DIGITS]"
        :maxlength="\App\Livewire\Forms\CarrierForm::RUC_DIGITS"
        label="RUC"
        button-label="Consultar RUC"
    />
</div>

<x-document-lookup.notices noun="RUC" />

{{-- A disabled fieldset disables the input inside it while the RUC is being looked up. --}}
<fieldset wire:loading.attr="disabled" wire:target="lookup" class="m-0 min-w-0 border-0 p-0">
    <x-wire-input id="{{ $idPrefix }}-business-name" wire:model="form.businessName" label="Razón social" type="text" maxlength="255" required autocomplete="off" />
</fieldset>

<x-wire-input id="{{ $idPrefix }}-address" wire:model="form.address" label="Dirección (opcional)" type="text" maxlength="255" autocomplete="off" />

<div class="grid gap-4 md:grid-cols-2">
    <x-wire-input id="{{ $idPrefix }}-email" wire:model="form.email" label="Correo electrónico (opcional)" type="email" maxlength="255" autocomplete="off" />
    <x-wire-input id="{{ $idPrefix }}-phone" wire:model="form.phone" label="Teléfono (opcional)" type="tel" maxlength="20" autocomplete="off" />
</div>
