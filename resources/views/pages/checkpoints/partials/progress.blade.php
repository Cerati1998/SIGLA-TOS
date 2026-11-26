{{-- How far a checkpoint went in the list (H36, criterion 2): its complete steps and its series. --}}
@php
    $progress = $checkpoint->progress();
    $status = $checkpoint->status();
    $complete = $progress['steps'] > 0 && $progress['stepsDone'] === $progress['steps'];
    $reinspections = $checkpoint->reinspections->count();
@endphp
<div class="flex flex-col gap-1 text-sm whitespace-nowrap">
    <span class="inline-flex items-center gap-1.5">
        <span class="inline-block h-1.5 w-15 overflow-hidden rounded-full bg-line" aria-hidden="true">
            <span class="block h-full {{ $complete ? 'bg-badge-success-ink' : 'bg-logo-orange' }}" style="width: {{ $progress['steps'] === 0 ? 0 : round($progress['stepsDone'] * 100 / $progress['steps']) }}%"></span>
        </span>
        Pasos {{ $progress['stepsDone'] }}/{{ $progress['steps'] }}
    </span>

    @if ($progress['series'] === 0)
        <span class="text-ink-muted">Sin series</span>
    @elseif ($progress['incorrect'] > 0)
        <span class="font-semibold text-badge-danger-ink">Series {{ $progress['correct'] }}/{{ $progress['series'] }} · {{ trans_choice(':count incorrecta|:count incorrectas', $progress['incorrect']) }}</span>
    @elseif ($status === \App\Enums\CheckpointStatus::Closed)
        <span>Series {{ $progress['correct'] }}/{{ $progress['series'] }} correctas{{ $reinspections > 0 ? ' · '.trans_choice(':count reinspección|:count reinspecciones', $reinspections) : '' }}</span>
    @else
        <span>Series {{ $progress['marked'] }}/{{ $progress['series'] }} marcadas</span>
    @endif
</div>
