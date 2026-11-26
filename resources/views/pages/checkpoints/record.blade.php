@php
    use App\Enums\CheckpointStatus;

    $lima = fn ($date) => $date?->copy()->timezone('America/Lima');
    $value = fn (?string $text): string => $text === null || $text === '' ? '—' : $text;
    $at = fn ($date): string => $lima($date)?->format('d/m/Y').' a las '.$lima($date)?->format('H:i');
    $certification = $checkpoint->certification;
    $status = $checkpoint->status();
    $stepsDone = $steps->filter(fn ($step) => $step->isComplete())->count();
    $started = 'Iniciado el '.$at($checkpoint->created_at);
    $unit = [
        'Cliente' => $value($certification->client),
        'Línea naviera' => $certification->shippingLine->name,
        'Booking' => $certification->booking,
        'Certificación' => 'Cerrada el '.$lima($certification->closed_at)?->format('d/m/Y').' · '.$certification->kit->pouch_code,
    ];
    $transport = [
        'Transportista' => $value($certification->carrier?->business_name),
        'Conductor' => $value($certification->driver?->full_name),
        'Remolcador' => $value($certification->tractor?->plate),
        'Semirremolque' => $value($certification->semiTrailer?->plate),
    ];
@endphp

<div x-data="checkpointRecord" class="flex flex-col gap-5.25">
    <div class="flex flex-wrap items-center justify-end gap-3 max-md:flex-col max-md:items-stretch">
        <flux:button :href="route('intranet.checkpoints.index')" wire:navigate class="max-md:order-2 max-md:h-13">Volver a Checkpoints</flux:button>
        <flux:button :href="route('intranet.certifications.show', $certification)" wire:navigate class="border-brand! text-brand! max-md:order-1 max-md:h-13">Ver la certificación</flux:button>
    </div>

    @if ($status === CheckpointStatus::Closed)
        <p class="m-0 flex items-start gap-2 rounded-control bg-badge-success p-3.5 text-sm text-badge-success-ink">
            <x-feather name="check-circle" class="mt-0.5 size-4" />
            <span>
                <strong>Cerrado</strong> el {{ $at($checkpoint->closed_at) }} por <strong>{{ $checkpoint->closer?->name }}</strong>.
                {{ $started }}@if ($checkpoint->starter !== null) por <strong>{{ $checkpoint->starter->name }}</strong>@endif.
                @if ($reinspections->isNotEmpty())
                    Tuvo <strong>{{ trans_choice(':count reinspección|:count reinspecciones', $reinspections->count()) }}</strong>.
                @endif
            </span>
        </p>
    @elseif ($status === CheckpointStatus::InReinspection)
        @php $open = $checkpoint->openReinspection; @endphp
        <p class="m-0 flex items-start gap-2 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
            <x-feather name="alert-triangle" class="mt-0.5 size-4" />
            <span>
                <strong>En reinspección</strong> desde el {{ $at($open?->created_at) }}. Motivo: «{{ $open?->reason }}». El checkpoint sigue cuando se cierre la reinspección.
                {{ $started }}@if ($checkpoint->starter !== null) por <strong>{{ $checkpoint->starter->name }}</strong>@endif.
            </span>
        </p>
    @else
        <p class="m-0 flex items-start gap-2 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
            <x-feather name="info" class="mt-0.5 size-4" />
            <span>
                <strong>Pendiente</strong>: {{ $stepsDone }} de {{ $steps->count() }} pasos completos. Se completa en «Registrar checkpoint».
                {{ $started }}@if ($checkpoint->starter !== null) por <strong>{{ $checkpoint->starter->name }}</strong>@endif.
            </span>
        </p>
    @endif

    <flux:card class="flex flex-col gap-5.25 p-5.25! max-md:p-4!">
        <h3 class="m-0 font-heading text-h3 font-normal">Unidad</h3>
        <x-pages::certifications.data-grid :data="$unit" />

        <section class="flex flex-col gap-3.5 border-t border-line pt-5.25">
            <h4 class="m-0 font-heading text-base font-medium">Transporte</h4>
            <x-pages::certifications.data-grid :data="$transport" />
        </section>
    </flux:card>

    @if ($reinspections->isNotEmpty())
        <flux:card class="flex flex-col gap-3.5 p-5.25! max-md:p-4!">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h3 class="m-0 font-heading text-h3 font-normal">Reinspecciones</h3>
                <span class="text-sm text-ink-muted">{{ $reinspections->count() }}</span>
            </div>

            @foreach ($reinspections as $reinspection)
                @include('pages.checkpoints.partials.record-reinspection', [
                    'reinspection' => $reinspection,
                    'round' => $rounds[$reinspection->id],
                    'marks' => $secondMarks[$reinspection->id] ?? [],
                    'at' => $at,
                ])
            @endforeach
        </flux:card>
    @endif

    <flux:card class="flex flex-col gap-4 p-5.25! max-md:p-4!">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h3 class="m-0 font-heading text-h3 font-normal">Pasos</h3>
            <span class="text-sm text-ink-muted">{{ trans_choice(':count paso|:count pasos', $steps->count()) }} · {{ trans_choice(':count foto|:count fotos', $photos) }} · {{ trans_choice(':count serie|:count series', $series) }}</span>
        </div>

        @foreach ($steps as $step)
            @php
                $verifies = $step->section->verifiedSections->pluck('name')->join(', ', ' y ');
                $chip = $step->isVerification()
                    ? ['variant' => 'info', 'label' => 'Verificación', 'short' => trans_choice(':count serie|:count series', $step->marks->count())]
                    : ['variant' => 'neutral', 'label' => 'Fotos', 'short' => trans_choice(':count foto|:count fotos', $step->photos->count())];
            @endphp
            <div wire:key="step-{{ $step->id }}" class="flex flex-col border-t border-line pt-4 md:grid md:grid-cols-[minmax(0,18rem)_minmax(0,1fr)] md:gap-5">
                <button type="button" x-on:click="toggle({{ $step->id }})" x-bind:aria-expanded="isOpen({{ $step->id }})"
                    class="flex min-h-11 w-full items-center gap-2 text-left md:hidden">
                    <span class="min-w-0 flex-1 font-semibold">{{ $step->position }} · {{ $step->section->name }}</span>
                    <x-status-badge :variant="$chip['variant']">{{ $chip['short'] }}</x-status-badge>
                    <i class="fa-solid fa-chevron-down text-sm text-ink-muted transition" x-bind:class="isOpen({{ $step->id }}) && 'rotate-180'" aria-hidden="true"></i>
                </button>

                <div class="hidden flex-col gap-2 md:flex">
                    <strong class="font-semibold">{{ $step->position }} · {{ $step->section->name }}</strong>
                    <span><x-status-badge :variant="$chip['variant']">{{ $chip['label'] }}</x-status-badge></span>
                    @if ($step->isVerification() && $verifies !== '')
                        <span class="text-sm text-ink-muted">De {{ $verifies }}</span>
                    @endif
                </div>

                <div x-show="isOpen({{ $step->id }}) || wide" x-cloak class="flex flex-col gap-2.5 max-md:pt-2.5 max-md:pb-2">
                    @if ($step->isVerification())
                        @include('pages.checkpoints.partials.record-marks', ['rows' => $markRows[$step->id], 'deviceSections' => $deviceSections, 'lima' => $lima])
                    @else
                        @include('pages.checkpoints.partials.record-photos', ['photos' => $step->photos, 'label' => $step->section->name, 'lima' => $lima])
                    @endif
                </div>
            </div>
        @endforeach

        <div class="flex flex-col border-t border-line pt-4 md:grid md:grid-cols-[minmax(0,18rem)_minmax(0,1fr)] md:gap-5">
            <button type="button" x-on:click="toggle('extra')" x-bind:aria-expanded="isOpen('extra')"
                class="flex min-h-11 w-full items-center gap-2 text-left md:hidden">
                <span class="min-w-0 flex-1 font-semibold">Observaciones y fotos extra</span>
                <x-status-badge variant="neutral">{{ trans_choice(':count foto|:count fotos', $extraPhotos->count()) }}</x-status-badge>
                <i class="fa-solid fa-chevron-down text-sm text-ink-muted transition" x-bind:class="isOpen('extra') && 'rotate-180'" aria-hidden="true"></i>
            </button>

            <strong class="hidden font-semibold md:block">Observaciones y fotos extra</strong>

            <div x-show="isOpen('extra') || wide" x-cloak class="flex flex-col gap-2.5 max-md:pt-2.5 max-md:pb-2">
                @if ($checkpoint->observations === null || trim($checkpoint->observations) === '')
                    <span class="text-sm text-ink-muted">Sin observaciones.</span>
                @else
                    <p class="m-0 break-words whitespace-pre-line">«{{ $checkpoint->observations }}»</p>
                @endif

                @include('pages.checkpoints.partials.record-photos', ['photos' => $extraPhotos, 'label' => 'Fotos extra', 'lima' => $lima])
            </div>
        </div>

        <span class="border-t border-line pt-3 pb-1 text-sm text-ink-muted">Solo consulta; sin PDF por ahora.</span>
    </flux:card>

    <template x-teleport="body">
        <div x-show="viewer" x-cloak x-on:keydown.escape.window="viewer = null" class="fixed inset-0 z-50 flex flex-col bg-black/95 text-white" role="dialog" aria-modal="true" aria-label="Foto en grande">
            <div class="flex flex-none items-center justify-end p-3">
                <button type="button" x-on:click="viewer = null" class="inline-flex size-11 items-center justify-center rounded-control text-white" aria-label="Cerrar la foto">
                    <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="flex min-h-0 flex-1 items-center justify-center">
                <img x-bind:src="viewer?.url" alt="Foto del checkpoint" class="max-h-full max-w-full object-contain">
            </div>
            <p class="m-0 flex-none p-4 pb-6 text-sm">Subida por <strong x-text="viewer?.by"></strong> el <span x-text="viewer?.at"></span>.</p>
        </div>
    </template>
</div>

@script
<script>
    Alpine.data('checkpointRecord', () => ({
        open: {},
        viewer: null,
        wide: window.matchMedia('(min-width: 48rem)').matches,

        init() {
            const query = window.matchMedia('(min-width: 48rem)');
            query.addEventListener('change', (event) => this.wide = event.matches);
        },

        isOpen(key) {
            return this.open[key] === true;
        },

        toggle(key) {
            this.open[key] = ! this.isOpen(key);
        },

        show(url, by, at) {
            this.viewer = { url, by, at };
        },
    }));
</script>
@endscript
