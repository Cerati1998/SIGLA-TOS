@props([
    'name',
])

<svg {{ $attributes->class('flex-none') }} fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <use href="{{ asset('images/feather-sprite.svg') }}#{{ $name }}"></use>
</svg>
