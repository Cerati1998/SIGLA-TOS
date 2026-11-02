@props([
    'links',
])

@php
    $isAllowed = fn (array $link): bool => ! isset($link['can']) || Gate::any((array) $link['can']);
@endphp

@foreach (array_filter($links, $isAllowed) as $link)
    @isset($link['header'])
        <div class="px-5 pt-3 pb-1 text-xs tracking-wider text-sidebar-muted uppercase md:group-data-collapsed/panel:hidden">{{ $link['header'] }}</div>
    @elseif (isset($link['submenu']))
        <x-panel.menu-tree :icon="$link['icon']" :label="$link['name']" :active="$link['active'] ?? false">
            @foreach (array_filter($link['submenu'], $isAllowed) as $sublink)
                <x-panel.menu-subitem :href="$sublink['route']" :active="$sublink['active'] ?? false">{{ $sublink['name'] }}</x-panel.menu-subitem>
            @endforeach
        </x-panel.menu-tree>
    @else
        <x-panel.menu-item :icon="$link['icon']" :href="$link['route']" :active="$link['active'] ?? false">{{ $link['name'] }}</x-panel.menu-item>
    @endisset
@endforeach
