@props([
    'active' => false,
])

<a
    {{ $attributes->class([
        'flex min-h-11 items-center gap-2.5 ps-10 pe-4 text-base hover:text-white',
        'font-semibold text-white' => $active,
        'text-sidebar-muted' => ! $active,
    ]) }}
    @if ($active) aria-current="page" @endif
    wire:navigate
    x-on:click="menuOpen = false"
>
    <x-feather name="circle" class="size-2" />
    {{ $slot }}
</a>
