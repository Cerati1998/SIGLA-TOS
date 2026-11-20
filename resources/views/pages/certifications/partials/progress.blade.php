<div class="flex flex-col gap-1 text-sm whitespace-nowrap">
    @foreach (['internal' => 'Interna', 'external' => 'Externa'] as $key => $label)
        @php
            ['total' => $total, 'done' => $done] = $progress[$key];
            $complete = $total > 0 && $done === $total;
        @endphp
        <span class="inline-flex items-center gap-1.5">
            <span class="inline-block h-1.5 w-15 overflow-hidden rounded-full bg-line" aria-hidden="true">
                <span class="block h-full {{ $complete ? 'bg-badge-success-ink' : 'bg-logo-orange' }}" style="width: {{ $total === 0 ? 0 : round($done * 100 / $total) }}%"></span>
            </span>
            {{ $label }} {{ $done }}/{{ $total }}
        </span>
    @endforeach
</div>
