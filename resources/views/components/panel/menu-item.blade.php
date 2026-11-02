@props([
    'icon',
    'active' => false,
])

@php
    $tag = $attributes->has('href') ? 'a' : 'button';
@endphp

<{{ $tag }}
    {{ $attributes->class([
        'ms-5 flex min-h-11.25 items-center gap-2.5 rounded-s-full px-3.75 py-2.5 text-start text-base font-medium transition-colors hover:bg-white hover:text-brand',
        'md:group-data-collapsed/panel:mx-2.5 md:group-data-collapsed/panel:justify-center md:group-data-collapsed/panel:rounded-full md:group-data-collapsed/panel:px-0',
        'bg-white text-brand shadow-card' => $active,
        'text-white' => ! $active,
    ]) }}
    title="{{ trim(strip_tags($slot)) }}"
    @if ($tag === 'a')
        wire:navigate
        x-on:click="menuOpen = false"
    @else
        type="button"
    @endif
    @if ($active) aria-current="page" @endif
>
    <x-feather :name="$icon" class="size-4.5" />
    <span class="flex-1 md:group-data-collapsed/panel:hidden">{{ $slot }}</span>
    {{ $trailing ?? '' }}
</{{ $tag }}>
