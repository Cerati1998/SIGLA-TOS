@props([
    'title',
    'breadcrumbs' => [],
])

<div {{ $attributes->class('flex flex-wrap items-center gap-x-5.25 gap-y-1') }}>
    <x-page-title>{{ $title }}</x-page-title>

    @if (count($breadcrumbs) > 1)
        <flux:breadcrumbs>
            @foreach ($breadcrumbs as $breadcrumb)
                @isset($breadcrumb['route'])
                    <flux:breadcrumbs.item :href="$breadcrumb['route']" separator="minus" wire:navigate>{{ $breadcrumb['name'] }}</flux:breadcrumbs.item>
                @else
                    <flux:breadcrumbs.item separator="minus">{{ $breadcrumb['name'] }}</flux:breadcrumbs.item>
                @endisset
            @endforeach
        </flux:breadcrumbs>
    @endif

    @if ($slot->isNotEmpty())
        <div class="ms-auto flex flex-wrap gap-2.5 max-md:w-full">{{ $slot }}</div>
    @endif
</div>
