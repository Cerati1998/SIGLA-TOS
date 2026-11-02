@props([
    'title',
])

<div {{ $attributes->class('flex flex-col gap-1') }}>
    @isset($breadcrumbs)
        <flux:breadcrumbs>{{ $breadcrumbs }}</flux:breadcrumbs>
    @endisset

    <x-page-title>{{ $title }}</x-page-title>
</div>
