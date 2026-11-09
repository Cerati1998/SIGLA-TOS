@props([
    'idPrefix',
])

@php
    $documentTypes = collect(\App\Livewire\Forms\ProviderForm::DOCUMENT_TYPES);
@endphp

<div class="grid gap-4 md:grid-cols-[200px_minmax(0,1fr)]">
    <x-wire-native-select id="{{ $idPrefix }}-identity" wire:model="form.identityId" label="Tipo de documento" :options="$documentTypes->map(fn (array $type): string => $type['label'])" option-key-value required />

    <x-document-lookup.number id="{{ $idPrefix }}-document-number" :digits="$documentTypes->map(fn (array $type): int => $type['digits'])" maxlength="11" />
</div>

<x-document-lookup.notices />

{{-- A disabled fieldset disables the input inside it while the document is being looked up. --}}
<fieldset wire:loading.attr="disabled" wire:target="lookup" class="m-0 min-w-0 border-0 p-0">
    <x-wire-input id="{{ $idPrefix }}-business-name" wire:model="form.businessName" label="Razón social" type="text" maxlength="255" required autocomplete="off" />
</fieldset>

<x-wire-input id="{{ $idPrefix }}-address" wire:model="form.address" label="Dirección (opcional)" type="text" maxlength="255" autocomplete="off" />

<div class="grid gap-4 md:grid-cols-2">
    <x-wire-input id="{{ $idPrefix }}-email" wire:model="form.email" label="Correo electrónico (opcional)" type="email" maxlength="255" autocomplete="off" />
    <x-wire-input id="{{ $idPrefix }}-phone" wire:model="form.phone" label="Teléfono (opcional)" type="tel" maxlength="20" autocomplete="off" />
</div>
