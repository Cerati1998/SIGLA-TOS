@props([
    'idPrefix',
    'identities',
    'branches',
    'lookup' => false,
])

<x-wire-native-select id="{{ $idPrefix }}-identity" wire:model="form.identityId" label="Tipo de documento" placeholder="Selecciona" :options="$identities->pluck('description', 'id')" option-key-value required />

@if ($lookup)
    <x-document-lookup.number id="{{ $idPrefix }}-document-number" :digits="\App\Livewire\Forms\UserForm::LOOKUP_DIGITS" maxlength="15" />

    <x-document-lookup.notices />

    {{-- A disabled fieldset disables the input inside it while the document is being looked up. --}}
    <fieldset wire:loading.attr="disabled" wire:target="lookup" class="m-0 min-w-0 border-0 p-0">
        <x-wire-input id="{{ $idPrefix }}-name" wire:model="form.name" label="Nombre" type="text" required autocomplete="off" />
    </fieldset>
@else
    <x-wire-input id="{{ $idPrefix }}-document-number" wire:model="form.documentNumber" label="Número de documento" type="text" maxlength="15" required autocomplete="off" />
    <x-wire-input id="{{ $idPrefix }}-name" wire:model="form.name" label="Nombre" type="text" required autocomplete="off" />
@endif

<x-wire-input id="{{ $idPrefix }}-email" wire:model="form.email" label="Correo electrónico" type="email" required autocomplete="off" />
<x-wire-native-select id="{{ $idPrefix }}-branch" wire:model="form.branchId" label="Sucursal" placeholder="Selecciona" :options="$branches" option-value="id" option-label="name" required />
