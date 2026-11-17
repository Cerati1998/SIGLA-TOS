<span class="flex flex-col items-start gap-0.5">
    <x-status-badge :variant="$bulkDispatch->reason->badgeVariant()">{{ $bulkDispatch->reason->label() }}</x-status-badge>
    <small class="text-[0.8125rem] text-ink-muted">{{ $bulkDispatch->destination() }}</small>
</span>
