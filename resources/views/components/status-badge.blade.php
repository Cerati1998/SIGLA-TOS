@props([
    'variant' => 'neutral',
])

<span {{ $attributes->class([
    'inline-block rounded-badge px-2 py-1 text-sm font-normal',
    match ($variant) {
        'success' => 'bg-badge-success text-badge-success-ink',
        'info' => 'bg-badge-info text-badge-info-ink',
        'warning' => 'bg-badge-warning text-badge-warning-ink',
        'danger' => 'bg-badge-danger text-badge-danger-ink',
        default => 'bg-badge-neutral text-badge-neutral-ink',
    },
]) }}>{{ $slot }}</span>
