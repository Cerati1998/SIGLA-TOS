{{-- A verification step of the checkpoint: each series the certification left in the sections it verifies, marked as correct or incorrect (H34, criterion 6). In the second round it shows only the series a reinspection brought, and the ones already verified go below (H35, criterion 8). --}}
@php
    $toMark = $round === null ? $step->marks : $step->marks->where('reinspection_id', $round->id)->values();
    $verifiedBefore = $round === null ? collect() : $step->marks->where('reinspection_id', '!==', $round->id)->values();
    $marked = $toMark->filter(fn ($mark) => $mark->is_correct !== null)->count();
    $wrong = $toMark->filter(fn ($mark) => $mark->is_correct === false)->count();
    $verified = $step->section->verifiedSections->map(fn ($section) => $section->name)->join(', ', ' y ');
@endphp

<div class="flex flex-col gap-2">
    <h3 class="m-0 font-heading text-h3 font-medium text-brand">{{ $step->section->name }}</h3>
    <div class="flex flex-wrap gap-2">
        @if ($round !== null)
            <x-status-badge variant="highlight">Vuelve de reinspección</x-status-badge>
        @endif
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
        <strong class="font-heading text-base font-medium">{{ $round === null ? 'Series a verificar' : ($toMark->count() === 1 ? 'Serie reemplazada' : 'Series reemplazadas') }}</strong>
        @if ($toMark->isNotEmpty())
            <span class="text-sm text-ink-muted" aria-live="polite">{{ $marked }} de {{ $toMark->count() }} marcadas</span>
        @endif
    </div>

    @if ($toMark->isEmpty())
        <p class="m-0 text-sm text-ink-muted">Sin dispositivos que verificar.</p>
    @else
        <p class="m-0 text-sm text-ink-muted">Marca <strong>Correcta</strong> si está en su lugar, intacta y con esta serie. Si está rota, falta o tiene otra serie, marca <strong>Incorrecta</strong>.</p>

        @foreach ($toMark as $mark)
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
                @isset ($replacedSerials[$mark->security_device_id])
                    <span class="text-[13px] text-ink-muted">Reemplaza a <s>{{ $replacedSerials[$mark->security_device_id] }}</s>, descartada en la reinspección.</span>
                @endisset
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

        @if ($verifiedBefore->isNotEmpty())
            <div class="flex flex-col pt-1">
                <span class="pb-1 text-sm font-medium text-ink-muted">Ya verificadas en la primera vuelta</span>
                @foreach ($verifiedBefore as $mark)
                    <div wire:key="verified-{{ $mark->id }}" class="flex items-center justify-between gap-2.5 border-t border-line py-2 text-[15px]">
                        <span>{{ $mark->device->serial_number }}</span>
                        <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-badge-success-ink"><i class="fa-solid fa-check text-xs" aria-hidden="true"></i> Correcta</span>
                    </div>
                @endforeach
            </div>
        @endif

        <p class="m-0 text-[13px] text-ink-muted">Series que la certificación dejó en {{ $verified }}. La marca se guarda al tocarla y se puede cambiar.</p>
    @endif
</div>

@if ($wrong > 0)
    <p class="m-0 flex gap-2.5 rounded-control bg-badge-warning p-3 text-sm text-badge-warning-ink">
        <x-feather name="alert-triangle" class="mt-0.5 size-4.5 shrink-0" />
        <span>{{ $wrong === 1 ? 'Hay 1 serie incorrecta.' : 'Hay '.$wrong.' series incorrectas.' }} Puedes seguir con los demás pasos: con una serie incorrecta el checkpoint no se cierra e inicias la reinspección.</span>
    </p>
@endif
