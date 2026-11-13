@props([
    'idPrefix',
    'identities',
    'providers',
    'lookup' => false,
])

<div class="grid gap-4 md:grid-cols-[200px_minmax(0,1fr)]">
    <x-wire-native-select id="{{ $idPrefix }}-identity" wire:model="form.identityId" label="Tipo de documento" :options="$identities->pluck('description', 'id')" option-key-value required />

    @if ($lookup)
        <x-document-lookup.number id="{{ $idPrefix }}-document-number" :digits="\App\Livewire\Forms\WorkerForm::LOOKUP_DIGITS" maxlength="15" button-label="Consultar DNI" />
    @else
        <x-wire-input id="{{ $idPrefix }}-document-number" wire:model="form.documentNumber" label="Número de documento" type="text" maxlength="15" required autocomplete="off" />
    @endif
</div>

@if ($lookup)
    <x-document-lookup.notices />

    {{-- A disabled fieldset disables the input inside it while the document is being looked up. --}}
    <fieldset wire:loading.attr="disabled" wire:target="lookup" class="m-0 min-w-0 border-0 p-0">
        <x-wire-input id="{{ $idPrefix }}-full-name" wire:model="form.fullName" label="Nombres y apellidos" type="text" maxlength="255" required autocomplete="off" />
    </fieldset>
@else
    <x-wire-input id="{{ $idPrefix }}-full-name" wire:model="form.fullName" label="Nombres y apellidos" type="text" maxlength="255" required autocomplete="off" />
@endif

<fieldset class="m-0 flex flex-col gap-1.75 border-0 p-0">
    <legend class="mb-1.75 p-0 text-sm font-medium text-ink">Procedencia</legend>
    <div class="grid grid-cols-2 gap-2.5">
        @foreach ([\App\Livewire\Forms\WorkerForm::OWN => 'Propio', \App\Livewire\Forms\WorkerForm::FROM_PROVIDER => 'De un proveedor'] as $value => $label)
            <label for="{{ $idPrefix }}-origin-{{ $value }}" class="flex min-h-11 cursor-pointer items-center gap-2 rounded-control border border-control px-3 py-2 has-checked:border-2 has-checked:border-brand has-checked:bg-badge-info md:min-h-10.25">
                <input id="{{ $idPrefix }}-origin-{{ $value }}" type="radio" wire:model="form.origin" value="{{ $value }}" class="size-4 accent-brand">
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>
    <flux:error name="form.origin" />
</fieldset>

<div x-show="$wire.form.origin === '{{ \App\Livewire\Forms\WorkerForm::FROM_PROVIDER }}'" x-cloak class="grid gap-1.75">
    <x-wire-native-select id="{{ $idPrefix }}-provider" wire:model="form.providerId" label="Proveedor" placeholder="Elige un proveedor" :options="$providers" option-key-value />
    <span class="text-sm text-ink-muted">Solo proveedores activos.</span>
</div>
