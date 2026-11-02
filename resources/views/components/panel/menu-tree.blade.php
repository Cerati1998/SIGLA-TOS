@props([
    'icon',
    'label',
    'active' => false,
])

<div @if ($active) x-init="openTree = @js($label)" @endif class="flex flex-col gap-1.25">
    <x-panel.menu-item
        :icon="$icon"
        :active="$active"
        x-on:click="menuCollapsed = false; openTree = openTree === {{ Js::from($label) }} ? null : {{ Js::from($label) }}"
    >
        {{ $label }}

        <x-slot:trailing>
            <span x-bind:class="{ 'rotate-90': openTree === {{ Js::from($label) }} }" class="transition-transform md:group-data-collapsed/panel:hidden">
                <x-feather name="chevron-right" class="size-4" />
            </span>
        </x-slot:trailing>
    </x-panel.menu-item>

    <div x-show="openTree === {{ Js::from($label) }}" x-collapse x-cloak class="md:group-data-collapsed/panel:hidden">
        <div class="flex flex-col">
            {{ $slot }}
        </div>
    </div>
</div>
