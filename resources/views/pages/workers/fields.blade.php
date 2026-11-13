@props([
    'idPrefix',
    'identities',
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

<x-wire-input id="{{ $idPrefix }}-position" wire:model="form.position" label="Cargo (opcional)" type="text" maxlength="100" autocomplete="off" />

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

{{-- A provider created with «Agregar» is chosen here: the provider window tells its id with the provider-created event. --}}
<div
    x-show="$wire.form.origin === '{{ \App\Livewire\Forms\WorkerForm::FROM_PROVIDER }}'"
    x-cloak
    x-on:provider-created.window="$wire.form.providerId = $event.detail.providerId"
    class="grid gap-1.75"
>
    <x-wire-select
        id="{{ $idPrefix }}-provider"
        wire:model="form.providerId"
        label="Proveedor"
        placeholder="Busca por razón social o documento"
        :async-data="['api' => route('intranet.providers.options'), 'method' => 'POST']"
        option-label="name"
        option-value="id"
    >
        <x-slot:corner>
            <x-label-link icon="plus" x-on:click="$openModal('provider-create')">Agregar</x-label-link>
        </x-slot:corner>
    </x-wire-select>
    <span class="text-sm text-ink-muted">Solo proveedores activos.</span>
</div>
