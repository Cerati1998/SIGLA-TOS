@props([
    'title',
])

<div {{ $attributes->class('flex flex-wrap items-center gap-x-5.25 gap-y-1') }}>
    <x-page-title>{{ $title }}</x-page-title>

    @isset($breadcrumbs)
        <flux:breadcrumbs>{{ $breadcrumbs }}</flux:breadcrumbs>
    @endisset
</div>
