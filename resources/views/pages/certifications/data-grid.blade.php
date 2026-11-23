@props(['data'])

<dl class="m-0 grid grid-cols-2 gap-4 md:grid-cols-4">
    @foreach ($data as $label => $detail)
        <div wire:key="data-{{ $loop->index }}" class="flex min-w-0 flex-col gap-0.5">
            <dt class="text-xs tracking-wider text-ink-muted uppercase">{{ $label }}</dt>
            <dd class="m-0 break-words">{{ $detail }}</dd>
        </div>
    @endforeach
</dl>
