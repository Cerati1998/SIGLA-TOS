@props([
    'title',
])

<div {{ $attributes->class('flex flex-wrap items-center gap-x-4 gap-y-1') }}>
    <x-page-title>{{ $title }}</x-page-title>

    @isset($breadcrumbs)
        <flux:breadcrumbs>{{ $breadcrumbs }}</flux:breadcrumbs>
    @endisset
</div>
