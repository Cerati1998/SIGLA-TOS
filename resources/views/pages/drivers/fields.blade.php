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

{{-- Notices of a driver that is already registered, when it is added to a carrier. --}}
<div role="status" x-show="['registered', 'inactive_in_carrier'].includes($wire.form.lookupStatus)" x-cloak>
    <p x-show="$wire.form.lookupStatus === 'registered'" class="m-0 flex items-center gap-2 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        Este conductor ya está registrado. Se agregará a este transportista con sus datos.
    </p>
    <p x-show="$wire.form.lookupStatus === 'inactive_in_carrier'" class="m-0 flex items-center gap-2 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        Este conductor está desactivado en este transportista. Guardar lo reactiva.
    </p>
</div>

{{-- The data of a registered driver is shown but not changed here: it changes with Editar. --}}
<fieldset x-bind:disabled="['registered', 'in_carrier', 'inactive_in_carrier'].includes($wire.form.lookupStatus)" class="m-0 grid min-w-0 gap-4 border-0 p-0">
    {{-- A disabled fieldset disables the input inside it while the DNI is being looked up. --}}
    <fieldset wire:loading.attr="disabled" wire:target="lookup" class="m-0 min-w-0 border-0 p-0">
        <x-wire-input id="{{ $idPrefix }}-full-name" wire:model="form.fullName" label="Nombres y apellidos" type="text" maxlength="255" required autocomplete="off" />
    </fieldset>

    <div class="md:max-w-75">
        <x-wire-input id="{{ $idPrefix }}-license" wire:model="form.license" label="Brevete" description="Hasta 15 caracteres. Se guarda en mayúsculas y sin espacios." type="text" maxlength="15" required autocomplete="off" />
    </div>
</fieldset>
