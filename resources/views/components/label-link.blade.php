@props([
    'icon' => null,
])

{{--
    Text link that goes at the other end of the label of a field, in the `corner` slot of a WireUI input or select,
    for an action on that field such as «Agregar». It is only text, without the shape of a button, with an optional
    Font Awesome icon (solid) on the left: `icon="plus"`. The link does not navigate: the action goes in `x-on:click`,
    and on a phone the touch area grows to 44 px.
--}}
<a
    href="#"
    x-on:click.prevent.stop=""
    {{ $attributes->class('inline-flex items-center gap-1.5 text-sm font-medium text-brand hover:text-highlight hover:underline max-md:min-h-11') }}
>
    @if ($icon)
        <i class="fa-solid fa-{{ $icon }} text-xs" aria-hidden="true"></i>
    @endif
    {{ $slot }}
</a>
