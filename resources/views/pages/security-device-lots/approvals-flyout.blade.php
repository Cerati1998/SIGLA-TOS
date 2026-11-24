@props([
    'lot',
    'approved',
])

<flux:modal name="lot-approvals" flyout class="flex w-full flex-col gap-0 p-0! md:w-110">
    <div class="flex flex-col gap-1 border-b border-line p-5 pe-14">
        <h3 class="m-0 font-heading text-h3 font-normal text-brand">Historial de aprobaciones</h3>
        <span class="text-sm text-ink-muted">Lote {{ $lot->name() }} · {{ number_format($approved, 0, ',', ' ') }} de {{ number_format($lot->quantity, 0, ',', ' ') }} aprobados</span>
    </div>

    @if ($lot->approvals->isEmpty())
        <p role="status" class="m-5 flex gap-2.5 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
            <x-feather name="info" class="mt-0.5 size-4.5 shrink-0" />
            <span>Todavía no se registró ninguna aprobación de este lote. Sus dispositivos siguen Inactivos.</span>
        </p>
    @else
        <ol aria-label="Aprobaciones del lote" class="m-0 flex list-none flex-col p-0">
            @foreach ($lot->approvals as $approval)
                <li class="flex flex-col gap-2 border-b border-line px-5 py-4">
                    <div class="flex items-baseline justify-between gap-2">
                        <strong class="font-heading text-base font-medium text-brand">Del {{ $approval->correlative_start }} al {{ $approval->correlative_end }}</strong>
                        <x-status-badge variant="success">{{ number_format($approval->approved_count, 0, ',', ' ') }} {{ $approval->approved_count === 1 ? 'aprobado' : 'aprobados' }}</x-status-badge>
                    </div>
                    <span class="text-sm">{{ $lot->serialNumber($approval->correlative_start) }} – {{ $lot->serialNumber($approval->correlative_end) }}</span>
                    <span class="text-sm text-ink-muted">{{ $approval->created_at?->timezone('America/Lima')->format('d/m/Y H:i') }} · {{ $approval->approver->name }}</span>
                </li>
            @endforeach
        </ol>
    @endif

    <p class="m-0 mt-auto border-t border-line px-5 py-4 text-sm text-ink-muted">Del más reciente al más antiguo. Las aprobaciones no se deshacen.</p>
</flux:modal>
