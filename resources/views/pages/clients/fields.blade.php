@props([
    'idPrefix',
])

@php
    $documentTypes = collect(\App\Livewire\Forms\ClientForm::DOCUMENT_TYPES);
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

<fieldset class="m-0 flex flex-col gap-1.75 border-0 p-0">
    <legend class="mb-1.75 p-0 text-sm font-medium text-ink">Condición de pago</legend>
    <div class="grid grid-cols-2 gap-2.5">
        @foreach (\App\Enums\PaymentCondition::cases() as $condition)
            <label for="{{ $idPrefix }}-payment-{{ $condition->value }}" class="flex min-h-11 cursor-pointer items-center gap-2 rounded-control border border-control px-3 py-2 has-checked:border-2 has-checked:border-brand has-checked:bg-badge-info md:min-h-10.25">
                <input id="{{ $idPrefix }}-payment-{{ $condition->value }}" type="radio" wire:model="form.paymentCondition" value="{{ $condition->value }}" class="size-4 accent-brand">
                <span>{{ $condition->label() }}</span>
            </label>
        @endforeach
    </div>
    <flux:error name="form.paymentCondition" />
</fieldset>

<div x-show="$wire.form.paymentCondition === '{{ \App\Enums\PaymentCondition::Credit->value }}'" x-cloak class="md:max-w-50">
    <x-wire-input id="{{ $idPrefix }}-credit-days" wire:model="form.creditDays" label="Días de crédito" type="text" inputmode="numeric" maxlength="3" autocomplete="off" />
</div>
