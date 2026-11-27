{{-- A photo step of the checkpoint: from 1 to n photos, with the rules of the inspection (H34, criterion 5). --}}
<div class="flex flex-col gap-2">
    <h3 class="m-0 font-heading text-h3 font-medium text-brand">{{ $step->section->name }}</h3>
    <div class="flex flex-wrap gap-2">
        <x-status-badge variant="info">Fotos</x-status-badge>
        @if ($step->isComplete())
            <x-status-badge variant="success">Con foto</x-status-badge>
        @else
            <x-status-badge variant="warning">Sin foto todavía</x-status-badge>
        @endif
    </div>
</div>

<div class="flex flex-col gap-3.5 rounded-card bg-white p-4 shadow-card">
    <div class="flex items-baseline justify-between gap-2">
        <strong class="font-heading text-base font-medium">Fotos</strong>
        <span class="text-sm text-ink-muted">{{ $step->photos->count() }} · mínimo 1</span>
    </div>

    <button type="button" x-show="canCapture" x-on:click="takePhoto({{ $step->id }})"
        class="inline-flex h-16 w-full items-center justify-center gap-2.5 rounded-control bg-highlight px-4 text-lg font-medium text-white">
        <x-feather name="camera" class="size-6.5" />
        Tomar foto
    </button>
    <p x-show="canCapture" class="m-0 text-sm text-ink-muted">Se abre la cámara del teléfono. Al volver, la foto se sube sola y puedes tomar la siguiente. Toca una foto para verla o eliminarla.</p>

    @include('pages.checkpoints.partials.photo-grid', ['photos' => $step->photos, 'stepId' => $step->id, 'label' => $step->section->name])

    @if ($step->photos->isEmpty())
        <p x-show="! canCapture" class="m-0 text-sm text-ink-muted">Todavía no tiene fotos.</p>
    @endif
</div>
