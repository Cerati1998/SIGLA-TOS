@props([
    'label',
    'model',
    'accept',
    'button',
    'hint',
])

<div x-data class="col-span-full flex flex-col gap-1.75">
    <flux:label>{{ $label }}</flux:label>

    <input x-ref="file" type="file" wire:model="{{ $model }}" accept="{{ $accept }}" aria-label="{{ $label }}" class="hidden">

    <div class="flex flex-wrap items-center gap-4">
        {{ $slot }}

        <flux:button type="button" x-on:click="$refs.file.value = ''; $refs.file.click()" wire:loading.attr="disabled" wire:target="{{ $model }}">
            <span wire:loading.remove wire:target="{{ $model }}">{{ $button }}</span>
            <span wire:loading wire:target="{{ $model }}">Subiendo…</span>
        </flux:button>
    </div>

    <span class="text-sm text-ink-muted">{{ $hint }}</span>

    <flux:error :name="$model" />
</div>
