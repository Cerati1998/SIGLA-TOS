@if ($certificationSection->sort_order === null)
    <span class="text-ink-muted">—</span>
@else
    <span class="inline-flex size-7 items-center justify-center rounded-full bg-table-head font-heading font-medium text-brand">{{ $certificationSection->sort_order }}</span>
@endif
