@props([
    'title',
    'description',
])

<div class="flex w-full flex-col gap-1 text-center">
    <x-page-title>{{ $title }}</x-page-title>
    <p class="m-0 text-base text-ink-muted">{{ $description }}</p>
</div>
