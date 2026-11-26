{{-- A verification step of the checkpoint: each series the certification left in the sections it verifies, marked as correct or incorrect (H34, criterion 6). --}}
@php
    $marked = $step->marks->filter(fn ($mark) => $mark->is_correct !== null)->count();
    $wrong = $step->marks->filter(fn ($mark) => $mark->is_correct === false)->count();
    $verified = $step->section->verifiedSections->map(fn ($section) => $section->name)->join(', ', ' y ');
@endphp

<div class="flex flex-col gap-2">
    <h3 class="m-0 font-heading text-h3 font-medium text-brand">{{ $step->section->name }}</h3>
    <div class="flex flex-wrap gap-2">
        <x-status-badge variant="neutral">Verificación</x-status-badge>
        @if ($step->isComplete())
            <x-status-badge variant="success">Completo</x-status-badge>
        @else
            <x-status-badge variant="warning">Falta marcar</x-status-badge>
        @endif
    </div>
</div>

<div class="flex flex-col gap-3 rounded-card bg-white px-4 py-3.5 shadow-card">
    <div class="flex items-baseline justify-between gap-2">
        <strong class="font-heading text-base font-medium">Series a verificar</strong>
        @if ($step->marks->isNotEmpty())
            <span class="text-sm text-ink-muted" aria-live="polite">{{ $marked }} de {{ $step->marks->count() }} marcadas</span>
        @endif
    </div>

    @if ($step->marks->isEmpty())
        <p class="m-0 text-sm text-ink-muted">Sin dispositivos que verificar.</p>
    @else
        <p class="m-0 text-sm text-ink-muted">Marca <strong>Correcta</strong> si está en su lugar, intacta y con esta serie. Si está rota, falta o tiene otra serie, marca <strong>Incorrecta</strong>.</p>

        @foreach ($step->marks as $mark)
            <div wire:key="mark-{{ $mark->id }}" @class([
                'flex flex-col gap-2.5 rounded-card p-3',
                'border border-line bg-white' => $mark->is_correct === null,
                'border-2 border-status-green bg-badge-success' => $mark->is_correct === true,
                'border-2 border-danger bg-badge-danger' => $mark->is_correct === false,
            ])>
                <div class="flex items-baseline justify-between gap-2">
                    <span class="font-heading text-[22px] font-medium tracking-wide">{{ $mark->device->serial_number }}</span>
                    <span class="text-right text-[13px] text-ink-muted">{{ $deviceSections[$mark->security_device_id] ?? '' }}</span>
                </div>
                <div class="grid grid-cols-2 gap-2" role="group" aria-label="Marca de la serie {{ $mark->device->serial_number }}">
                    <button type="button" wire:click="mark({{ $mark->id }}, true)" wire:loading.attr="disabled" wire:target="mark" @disabled($closed) aria-pressed="{{ $mark->is_correct === true ? 'true' : 'false' }}"
                        @class([
                            'inline-flex h-12 items-center justify-center gap-2 rounded-control text-base font-medium disabled:cursor-not-allowed',
                            'bg-status-green text-white' => $mark->is_correct === true,
                            'border border-control bg-white text-ink' => $mark->is_correct !== true,
                        ])>
                        <i class="fa-solid fa-check" aria-hidden="true"></i> Correcta
                    </button>
                    <button type="button" wire:click="mark({{ $mark->id }}, false)" wire:loading.attr="disabled" wire:target="mark" @disabled($closed) aria-pressed="{{ $mark->is_correct === false ? 'true' : 'false' }}"
                        @class([
                            'inline-flex h-12 items-center justify-center gap-2 rounded-control text-base font-medium disabled:cursor-not-allowed',
                            'bg-danger text-white' => $mark->is_correct === false,
                            'border border-control bg-white text-ink' => $mark->is_correct !== false,
                        ])>
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i> Incorrecta
                    </button>
                </div>
                @if ($mark->marker !== null && $mark->marked_at !== null)
                    <span class="text-xs text-ink-muted">Marcada por {{ $mark->marker->name }}, {{ $mark->marked_at->diffForHumans() }}</span>
                @endif
            </div>
        @endforeach

        <p class="m-0 text-[13px] text-ink-muted">Series que la certificación dejó en {{ $verified }}. La marca se guarda al tocarla y se puede cambiar.</p>
    @endif
</div>

@if ($wrong > 0)
    <p class="m-0 flex gap-2.5 rounded-control bg-badge-warning p-3 text-sm text-badge-warning-ink">
        <x-feather name="alert-triangle" class="mt-0.5 size-4.5 shrink-0" />
        <span>{{ $wrong === 1 ? 'Hay 1 serie incorrecta.' : 'Hay '.$wrong.' series incorrectas.' }} Puedes seguir con los demás pasos: con una serie incorrecta el checkpoint pasa a reinspección.</span>
    </p>
@endif
