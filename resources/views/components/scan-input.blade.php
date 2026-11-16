@props([
    'disabled' => false,
    'invalid' => false,
])

@php
    $tone = match (true) {
        $disabled => 'border-control bg-surface text-ink-muted',
        $invalid => 'border-danger bg-white text-ink',
        default => 'border-brand bg-white text-ink',
    };
@endphp

<div class="flex">
    <input
        {{ $attributes->merge(['type' => 'text', 'maxlength' => 50, 'autocomplete' => 'off', 'spellcheck' => 'false'])->class([
            'h-11 min-w-0 flex-1 rounded-s-control border-2 border-e-0 px-3 text-lg uppercase outline-hidden placeholder:text-base placeholder:normal-case placeholder:text-ink-muted',
            $tone,
        ]) }}
        @disabled($disabled)
        @if ($invalid) aria-invalid="true" @endif
    />
    <span aria-hidden="true" @class(['inline-flex h-11 w-12 flex-none items-center justify-center rounded-e-control border-2 border-s-0', $tone, 'bg-badge-info! text-brand!' => ! $disabled && ! $invalid])>
        <x-feather name="maximize" class="size-5" />
    </span>
</div>
