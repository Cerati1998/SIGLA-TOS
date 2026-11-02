<flux:card {{ $attributes->class('flex flex-col p-0!') }}>
    @isset($filters)
        <div class="flex flex-wrap items-end gap-3 border-b border-line p-5.25">
            {{ $filters }}
        </div>
    @endisset

    {{ $slot }}

    @isset($footer)
        <div class="flex flex-wrap items-center justify-between gap-3 p-5.25 text-sm text-ink-muted">
            {{ $footer }}
        </div>
    @endisset
</flux:card>
