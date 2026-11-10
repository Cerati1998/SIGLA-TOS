{{-- Fields shared by the create and edit modals. --}}
<x-wire-input id="{{ $prefix }}-name" wire:model="form.name" label="Nombre" type="text" maxlength="100" required autofocus autocomplete="off" />

<fieldset class="m-0 flex flex-col gap-1.75 border-0 p-0">
    <legend class="mb-1.75 p-0 text-sm font-medium text-ink">Tipo</legend>
    <div class="grid grid-cols-2 gap-2.5">
        @foreach ($types as $value => $label)
            <label for="{{ $prefix }}-type-{{ $value }}" class="flex min-h-11 cursor-pointer items-center gap-2 rounded-control border border-control px-3 py-2 has-checked:border-2 has-checked:border-brand has-checked:bg-badge-info md:min-h-10.25">
                <input id="{{ $prefix }}-type-{{ $value }}" type="radio" wire:model="form.type" value="{{ $value }}" class="size-4 accent-brand">
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>
    <flux:error name="form.type" />
</fieldset>
