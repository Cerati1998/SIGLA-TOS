@props([
    'active' => false,
])

<a
    {{ $attributes->class([
        'ms-5 flex items-center py-1.25 ps-6.25 pe-1.25 text-base font-medium hover:text-white max-md:min-h-11',
        'font-semibold text-white' => $active,
        'text-sidebar-muted' => ! $active,
    ]) }}
    @if ($active) aria-current="page" @endif
    wire:navigate
    x-on:click="menuOpen = false"
>
    <x-feather name="circle" class="ms-2.5 me-5 size-2" />
    {{ $slot }}
</a>
