<span
    class="inline-flex size-6.5 flex-none items-center justify-center rounded-full"
    :class="{
        'bg-badge-success text-badge-success-ink': scan.state === 'added',
        'bg-badge-danger text-danger': scan.state === 'rejected' || scan.state === 'failed',
        'bg-badge-info text-brand': scan.state === 'pending',
    }"
>
    <x-feather name="check" class="size-3.5" x-show="scan.state === 'added'" />
    <x-feather name="x" class="size-3.5" x-show="scan.state === 'rejected' || scan.state === 'failed'" />
    <x-feather name="loader" class="size-3.5 animate-spin" x-show="scan.state === 'pending'" />
</span>
