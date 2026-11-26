{{-- The last step of the checkpoint: observations and extra photos, both optional, the summary and «Finalizar» (H34, criteria 7 to 9). --}}
<div class="flex flex-col gap-2">
    <h3 class="m-0 font-heading text-h3 font-medium text-brand">Observaciones y fotos extra</h3>
    <div class="flex flex-wrap gap-2"><x-status-badge variant="neutral">Opcional</x-status-badge></div>
</div>

<div class="flex flex-col gap-3.5 rounded-card bg-white p-4 shadow-card">
    <flux:textarea wire:model.blur="observations" label="Observaciones" rows="4" maxlength="2000" :disabled="$closed"
        placeholder="Escribe aquí lo que quieras dejar anotado de esta unidad." />

    <div class="flex items-baseline justify-between gap-2 border-t border-line pt-3.5">
        <strong class="font-heading text-base font-medium">Fotos extra</strong>
        <span class="text-sm text-ink-muted">{{ $extraPhotos->count() }}</span>
    </div>

    <button type="button" x-show="canCapture" x-on:click="takePhoto(null)"
        class="inline-flex h-13 w-full items-center justify-center gap-2.5 rounded-control border border-highlight bg-white px-4 text-base font-medium text-highlight">
        <x-feather name="camera" class="size-5.5" />
        Tomar foto extra
    </button>

    @include('pages.checkpoints.partials.photo-grid', ['photos' => $extraPhotos, 'stepId' => null, 'label' => 'Fotos extra'])

    <p class="m-0 text-[13px] text-ink-muted">Mismas reglas que las demás fotos: solo con la cámara, con fecha, hora y contenedor impresos.</p>
</div>

<div class="flex flex-col gap-1 rounded-card bg-white px-4 py-3.5 shadow-card">
    <strong class="pb-1.5 font-heading text-base font-medium">Resumen</strong>
    @php
        $photoDone = $photoSteps->filter(fn ($step) => $step->isComplete())->count();
        $correct = $marks->filter(fn ($mark) => $mark->is_correct === true)->count();
    @endphp
    <div class="flex items-center justify-between gap-2.5 py-2.5 text-[15px]">
        <span>Pasos de fotos</span>
        <span @class(['font-semibold', 'text-badge-success-ink' => $photoDone === $photoSteps->count(), 'text-badge-warning-ink' => $photoDone !== $photoSteps->count()])>{{ $photoDone }} de {{ $photoSteps->count() }} con foto</span>
    </div>
    <div class="flex items-center justify-between gap-2.5 border-t border-line py-2.5 text-[15px]">
        <span>Series correctas</span>
        <span class="font-semibold text-badge-success-ink">{{ $correct }} de {{ $marks->count() }}</span>
    </div>
    <div class="flex items-center justify-between gap-2.5 border-t border-line py-2.5 text-[15px]">
        <span>Series incorrectas</span>
        <span @class(['font-semibold', 'text-badge-danger-ink' => $incorrect->isNotEmpty()])>{{ $incorrect->count() }}</span>
    </div>

    @if ($incorrect->isNotEmpty())
        <ul class="m-0 flex list-none flex-col gap-1.5 rounded-lg bg-badge-danger px-3 py-2.5 text-sm" aria-label="Series incorrectas">
            @foreach ($incorrect as $mark)
                <li wire:key="wrong-{{ $mark->id }}"><strong class="font-heading font-medium">{{ $mark->device->serial_number }}</strong> · {{ $deviceSections[$mark->security_device_id] ?? '' }}</li>
            @endforeach
        </ul>
    @endif

    @if ($closed)
        <p class="m-0 pt-2.5 text-sm text-ink-muted">Checkpoint cerrado: no se edita.</p>
    @elseif ($incorrect->isNotEmpty())
        <p class="m-0 pt-2.5 text-sm text-badge-warning-ink">Hay series incorrectas: el checkpoint no se cierra y pasa a reinspección. Queda a medias, con sus marcas guardadas.</p>
    @elseif (! $complete)
        <p class="m-0 pt-2.5 text-sm text-ink-muted">Para finalizar falta completar pasos: cada paso de fotos necesita al menos una foto y cada serie, su marca.</p>
    @else
        <p class="m-0 pt-2.5 text-sm text-badge-success-ink">Todo está completo y todas las series son correctas: el checkpoint se puede cerrar.</p>
    @endif

    <flux:error name="checkpoint" />
</div>
