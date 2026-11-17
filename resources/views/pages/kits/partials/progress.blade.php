<div
    class="h-2 overflow-hidden rounded-full bg-table-head"
    role="progressbar"
    aria-label="{{ $label }}"
    aria-valuemin="0"
    aria-valuenow="{{ $added }}"
    aria-valuemax="{{ $quantity }}"
>
    <div
        @class(['h-full rounded-full', 'bg-badge-success-ink' => $added >= $quantity, 'bg-brand' => $added < $quantity])
        style="width: {{ $quantity === 0 ? 0 : min(100, (int) round($added * 100 / $quantity)) }}%"
    ></div>
</div>
