{{--
    A reinspection in the detail of a checkpoint (H36, criterion 3): its reason, who started and closed it and when, the
    sections it reopened, each device replaced with the extra one that came in, and the second verification.
--}}
@php
    $sections = $reinspection->devices->map(fn ($device) => $device->step->section->name)->unique()->join(', ', ' y ');
    $markText = fn ($mark): array => match ($mark->is_correct) {
        true => ['class' => 'text-badge-success-ink', 'icon' => 'check', 'label' => 'Correcta'],
        false => ['class' => 'text-badge-danger-ink', 'icon' => 'x', 'label' => 'Incorrecta'],
        default => ['class' => 'text-ink-muted', 'icon' => 'minus', 'label' => 'Sin marcar'],
    };
@endphp
<div wire:key="reinspection-{{ $reinspection->id }}" class="flex flex-col gap-2.5 rounded-r-lg border-l-4 border-highlight bg-badge-warning px-4 py-3.5 text-sm">
    <div class="flex flex-wrap items-center gap-2.5">
        <x-status-badge variant="highlight">Reinspección {{ $round }}</x-status-badge>
        <strong class="text-[15px]">{{ $reinspection->isClosed() ? 'Cerrada' : 'Abierta' }}</strong>
    </div>

    <dl class="m-0 flex flex-col gap-2.5 md:gap-2">
        <div class="flex flex-col gap-0.5 md:grid md:grid-cols-[9.5rem_minmax(0,1fr)] md:gap-3">
            <dt class="text-ink-muted max-md:text-xs max-md:tracking-wider max-md:uppercase">Motivo</dt>
            <dd class="m-0 break-words">«{{ $reinspection->reason }}».</dd>
        </div>
        <div class="flex flex-col gap-0.5 md:grid md:grid-cols-[9.5rem_minmax(0,1fr)] md:gap-3">
            <dt class="text-ink-muted max-md:text-xs max-md:tracking-wider max-md:uppercase">Iniciada</dt>
            <dd class="m-0">{{ $at($reinspection->created_at) }} por {{ $reinspection->starter->name }}, en el checkpoint</dd>
        </div>
        <div class="flex flex-col gap-0.5 md:grid md:grid-cols-[9.5rem_minmax(0,1fr)] md:gap-3">
            <dt class="text-ink-muted max-md:text-xs max-md:tracking-wider max-md:uppercase">{{ $reinspection->devices->pluck('certification_step_id')->unique()->count() === 1 ? 'Sección reabierta' : 'Secciones reabiertas' }}</dt>
            <dd class="m-0">{{ $sections === '' ? '—' : $sections }}</dd>
        </div>
        <div class="flex flex-col gap-0.5 md:grid md:grid-cols-[9.5rem_minmax(0,1fr)] md:gap-3">
            <dt class="text-ink-muted max-md:text-xs max-md:tracking-wider max-md:uppercase">{{ $reinspection->devices->count() === 1 ? 'Reemplazo' : 'Reemplazos' }}</dt>
            <dd class="m-0 flex flex-col gap-1">
                @foreach ($reinspection->devices as $device)
                    <span wire:key="reinspection-device-{{ $device->id }}">
                        <s>{{ $device->device->serial_number }}</s> →
                        @if ($device->replacement !== null)
                            <strong>{{ $device->replacement->installedDevice->serial_number }}</strong> · despacho a granel {{ $device->replacement->bulkDispatch->number() }} · la anterior quedó Descartada
                        @else
                            <span class="text-ink-muted">falta reemplazarla</span>
                        @endif
                    </span>
                @endforeach
            </dd>
        </div>
        <div class="flex flex-col gap-0.5 md:grid md:grid-cols-[9.5rem_minmax(0,1fr)] md:gap-3">
            <dt class="text-ink-muted max-md:text-xs max-md:tracking-wider max-md:uppercase">Cerrada</dt>
            <dd class="m-0">{{ $reinspection->isClosed() ? $at($reinspection->closed_at).' por '.$reinspection->closer?->name : 'Todavía no: se cierra en Asignaciones.' }}</dd>
        </div>
        @if ($reinspection->isClosed())
            <div class="flex flex-col gap-0.5 md:grid md:grid-cols-[9.5rem_minmax(0,1fr)] md:gap-3">
                <dt class="text-ink-muted max-md:text-xs max-md:tracking-wider max-md:uppercase">Segunda verificación</dt>
                <dd class="m-0 flex flex-col gap-1">
                    @forelse ($marks as $mark)
                        @php $text = $markText($mark); @endphp
                        <span wire:key="second-mark-{{ $mark->id }}">
                            {{ $mark->device->serial_number }} ·
                            <span class="inline-flex items-center gap-1 font-semibold {{ $text['class'] }}"><x-feather :name="$text['icon']" class="size-3.5" />{{ $text['label'] }}</span>
                            @if ($mark->marked_at !== null)
                                · {{ $at($mark->marked_at) }} por {{ $mark->marker?->name }}
                            @endif
                        </span>
                    @empty
                        <span class="text-ink-muted">—</span>
                    @endforelse
                </dd>
            </div>
        @endif
    </dl>
</div>
