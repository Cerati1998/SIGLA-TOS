@props([
    'active' => false,
    'disabled' => false,
])

@if ($disabled)
    <span
        {{ $attributes->class(['ms-5 flex cursor-not-allowed items-center py-1.25 ps-6.25 pe-1.25 text-base font-medium text-sidebar-muted opacity-60 max-md:min-h-11']) }}
        aria-disabled="true"
    >
        <x-feather name="circle" class="ms-2.5 me-5 size-2" />
        {{ $slot }}
    </span>
@else
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
@endif
