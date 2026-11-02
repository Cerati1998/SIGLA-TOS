@props([
    'active' => false,
])

<a
    {{ $attributes->class([
        'flex min-h-11 items-center border-l-4 px-5 text-base text-white hover:bg-white/10',
        'border-logo-orange bg-white/14 font-semibold' => $active,
        'border-transparent' => ! $active,
    ]) }}
    @if ($active) aria-current="page" @endif
    wire:navigate
    x-on:click="menuOpen = false"
>
    {{ $slot }}
</a>
