@props([
    'number',
    'title',
    'text',
])

<div class="flex items-start gap-3">
    <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-brand font-heading text-sm font-medium text-white" aria-hidden="true">{{ $number }}</span>
    <div>
        <h4 {{ $attributes->only('id') }} class="font-heading text-base font-medium text-brand">{{ $title }}</h4>
        <p class="mt-0.5 text-sm text-ink-muted">{{ $text }}</p>
    </div>
</div>
