@php
    use App\Enums\CertificationSectionType;

    $lima = fn ($date) => $date?->copy()->timezone('America/Lima');
    $value = fn (?string $text): string => $text === null || $text === '' ? '—' : $text;
    $schedule = function (array $inspection) use ($lima): string {
        if ($inspection['startedAt'] === null) {
            return 'Sin fotos todavía';
        }

        return $lima($inspection['startedAt'])->format('d/m/Y').' · '.$lima($inspection['startedAt'])->format('H:i').' a '.$lima($inspection['finishedAt'])->format('H:i');
    };
    $startedAt = $lima($certification->started_at);
    $request = [
        'Cliente' => $value($certification->client),
        'Producto' => $value($certification->product),
        'Línea naviera' => $certification->shippingLine->name,
        'Booking' => $certification->booking,
    ];
    $transport = [
        'Transportista' => $value($certification->carrier?->business_name),
        'Conductor' => $value($certification->driver?->full_name),
        'Remolcador' => $value($certification->tractor?->plate),
        'Semirremolque' => $value($certification->semiTrailer?->plate),
    ];
    $kit = [
        'Funda' => $certification->kit->pouch_code.' · '.$certification->kit->status->label(),
        'Formato' => $value($certification->inspection_sheet_code),
        'Tipo de kit' => $certification->kit->kitType->name,
        'Dispositivos' => (string) $devices,
    ];
@endphp

<div x-data="certificationDetails" class="flex flex-col gap-5.25">
    <div class="flex flex-wrap items-center justify-end gap-3 max-md:flex-col max-md:items-stretch">
        <flux:button :href="route('intranet.certifications.list')" wire:navigate class="max-md:order-2 max-md:h-13">Volver a Asignaciones</flux:button>
        @if ($certification->isClosed())
            <flux:button :href="route('intranet.certifications.pdf', $certification)" target="_blank" variant="primary" class="[--color-accent:var(--color-danger)] max-md:order-1 max-md:h-13">
                <i class="fa-solid fa-file-pdf text-sm" aria-hidden="true"></i>
                Descargar PDF
            </flux:button>
        @endif
    </div>

    @if ($certification->isAnnulled())
        @php $annulledAt = $lima($certification->annulled_at); @endphp
        <p class="m-0 flex items-start gap-2 rounded-control bg-badge-danger p-3.5 text-sm text-badge-danger-ink">
            <i class="fa-solid fa-ban mt-0.5" aria-hidden="true"></i>
            <span>
                <strong>Asignación anulada</strong> el {{ $annulledAt->format('d/m/Y') }} a las {{ $annulledAt->format('H:i') }} por <strong>{{ $certification->annuller?->name }}</strong>.
                Motivo: {{ $certification->annulment_reason }}. Decisión sobre el kit: <strong>{{ $certification->kit_decision?->label() }}</strong>.
                Iniciada el {{ $startedAt->format('d/m/Y') }} a las {{ $startedAt->format('H:i') }} por <strong>{{ $certification->certifier->name }}</strong>.
            </span>
        </p>
    @elseif ($certification->isClosed())
        @php $closedAt = $lima($certification->closed_at); @endphp
        <p class="m-0 flex items-start gap-2 rounded-control bg-badge-success p-3.5 text-sm text-badge-success-ink">
            <i class="fa-solid fa-lock mt-0.5" aria-hidden="true"></i>
            <span>
                <strong>Cerrada</strong> el {{ $closedAt->format('d/m/Y') }} a las {{ $closedAt->format('H:i') }} por <strong>{{ $certification->closer?->name }}</strong>.
                Iniciada el {{ $startedAt->format('d/m/Y') }} a las {{ $startedAt->format('H:i') }} por <strong>{{ $certification->certifier->name }}</strong>, en la zona <strong>{{ $certification->yardZone->name }}</strong>.
            </span>
        </p>
    @else
        <p class="m-0 flex items-start gap-2 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
            <i class="fa-solid fa-circle-info mt-0.5" aria-hidden="true"></i>
            <span>
                <strong>{{ $certification->status()->label() }}</strong>.
                Iniciada el {{ $startedAt->format('d/m/Y') }} a las {{ $startedAt->format('H:i') }} por <strong>{{ $certification->certifier->name }}</strong>, en la zona <strong>{{ $certification->yardZone->name }}</strong>.
            </span>
        </p>

        @if ($certification->lastReopening !== null)
            @php $reopenedAt = $lima($certification->lastReopening->created_at ?? now()); @endphp
            <p class="m-0 flex items-start gap-2 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
                <i class="fa-solid fa-lock-open mt-0.5" aria-hidden="true"></i>
                <span>
                    <strong>Reaperturada</strong> el {{ $reopenedAt->format('d/m/Y') }} a las {{ $reopenedAt->format('H:i') }}
                    por <strong>{{ $certification->lastReopening->reopener->name }}</strong>. Motivo: «{{ $certification->lastReopening->reason }}».
                </span>
            </p>
        @endif
    @endif

    <flux:card class="flex flex-col gap-5.25 p-5.25! max-md:p-4!">
        <h3 class="m-0 font-heading text-h3 font-normal">Solicitud {{ $certification->gate_out_request_reference }}</h3>

        <x-pages::certifications.data-grid :data="$request" />

        @foreach (['Transporte' => $transport, 'Kit' => $kit] as $title => $data)
            <section wire:key="block-{{ $loop->index }}" class="flex flex-col gap-3.5 border-t border-line pt-5.25">
                <h4 class="m-0 font-heading text-base font-medium">{{ $title }}</h4>
                <x-pages::certifications.data-grid :data="$data" />
            </section>
        @endforeach

        <section class="flex flex-col gap-3.5 border-t border-line pt-5.25">
            <h4 class="m-0 font-heading text-base font-medium">Inspecciones</h4>
            <dl class="m-0 grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach ($inspections as $inspection)
                    <div wire:key="inspector-{{ $inspection['type']->value }}" class="flex min-w-0 flex-col gap-0.5">
                        <dt class="text-xs tracking-wider text-ink-muted uppercase">{{ $inspection['type']->label() }} · {{ trans_choice(':count sección|:count secciones', $inspection['sections']) }}</dt>
                        <dd class="m-0 break-words">{{ $inspection['inspectors'] === [] ? '—' : implode(', ', $inspection['inspectors']) }}</dd>
                    </div>
                    <div wire:key="schedule-{{ $inspection['type']->value }}" class="flex min-w-0 flex-col gap-0.5">
                        <dt class="text-xs tracking-wider text-ink-muted uppercase">Horario</dt>
                        <dd class="m-0 break-words">{{ $schedule($inspection) }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    </flux:card>

    @unless ($certification->isReadOnly())
        <section class="grid gap-4 md:grid-cols-2">
            @foreach ($inspections as $inspection)
                @php $done = $steps->where('type', $inspection['type'])->filter(fn ($step) => $step->photos->isNotEmpty())->count(); @endphp
                <a wire:key="inspection-{{ $inspection['type']->value }}" href="{{ route('intranet.inspections.show', [$inspection['type'], $certification]) }}" wire:navigate
                    class="flex flex-col gap-2.5 rounded-card border border-line bg-white p-4 text-ink no-underline shadow-card hover:border-brand hover:text-ink">
                    <span class="flex items-center justify-between gap-2">
                        <strong class="font-heading text-lg font-medium text-brand">Inspección {{ mb_strtolower($inspection['type']->label()) }}</strong>
                        @if ($inspection['sections'] > 0 && $done === $inspection['sections'])
                            <x-status-badge variant="success">Completa</x-status-badge>
                        @elseif ($done > 0)
                            <x-status-badge variant="info">A medias</x-status-badge>
                        @else
                            <x-status-badge variant="warning">Sin empezar</x-status-badge>
                        @endif
                    </span>
                    <span class="h-1.5 overflow-hidden rounded-full bg-line" aria-hidden="true">
                        <span class="block h-full rounded-full {{ $done === $inspection['sections'] ? 'bg-badge-success-ink' : 'bg-logo-orange' }}"
                            style="width: {{ $inspection['sections'] === 0 ? 0 : round($done * 100 / $inspection['sections']) }}%"></span>
                    </span>
                    <span class="text-sm text-ink-muted">{{ $done }} de {{ $inspection['sections'] }} secciones con foto</span>
                </a>
            @endforeach
        </section>
    @endunless

    <flux:card class="flex flex-col gap-4 p-5.25! max-md:p-4!">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h3 class="m-0 font-heading text-h3 font-normal">Secciones</h3>
            <span class="text-sm text-ink-muted">{{ trans_choice(':count sección|:count secciones', $steps->count()) }} · {{ trans_choice(':count foto|:count fotos', $photos) }}</span>
        </div>

        @forelse ($steps as $step)
            @php
                $first = $step->photos->first();
                $last = $step->photos->last();
                $uploaders = $step->photos->map(fn ($photo) => $photo->uploader->name)->unique()->implode(', ');
            @endphp
            <div wire:key="step-{{ $step->id }}" class="flex flex-col border-t border-line pt-4 md:grid md:grid-cols-[minmax(0,18rem)_minmax(0,1fr)] md:gap-5">
                <button type="button" x-on:click="toggle({{ $step->id }})" x-bind:aria-expanded="isOpen({{ $step->id }})"
                    class="flex min-h-11 w-full items-center gap-2 text-left md:hidden">
                    <span class="min-w-0 flex-1 font-semibold">{{ $step->position }} · {{ $step->section->name }}</span>
                    <x-status-badge :variant="$step->type === CertificationSectionType::Internal ? 'purple' : 'info'">{{ $step->type->label() }}</x-status-badge>
                    <i class="fa-solid fa-chevron-down text-sm text-ink-muted transition" x-bind:class="isOpen({{ $step->id }}) && 'rotate-180'" aria-hidden="true"></i>
                </button>

                <div class="hidden flex-col gap-2 md:flex">
                    <strong class="font-semibold">{{ $step->position }} · {{ $step->section->name }}</strong>
                    <span><x-status-badge :variant="$step->type === CertificationSectionType::Internal ? 'purple' : 'info'">{{ $step->type->label() }}</x-status-badge></span>
                    @include('pages.certifications.partials.step-devices', ['step' => $step])
                </div>

                <div x-show="isOpen({{ $step->id }}) || wide" x-cloak class="flex flex-col gap-2.5 max-md:pt-2.5">
                    <div class="md:hidden">@include('pages.certifications.partials.step-devices', ['step' => $step])</div>

                    @if ($step->photos->isEmpty())
                        <span class="text-sm text-ink-muted">Sin fotos.</span>
                    @else
                        <div class="grid grid-cols-4 gap-2 sm:grid-cols-6">
                            @foreach ($step->photos as $photo)
                                <button type="button" wire:key="photo-{{ $photo->id }}"
                                    x-on:click="show(@js(route('intranet.certification-photos.show', $photo)), @js($photo->uploader->name), @js($lima($photo->created_at)?->format('d/m/Y H:i')))"
                                    class="aspect-square overflow-hidden rounded-control border border-line bg-surface max-md:min-h-11"
                                    aria-label="Ver la foto {{ $loop->iteration }} de {{ $step->section->name }}">
                                    <img src="{{ route('intranet.certification-photos.thumbnail', $photo) }}" alt="" loading="lazy" class="size-full object-cover">
                                </button>
                            @endforeach
                        </div>
                        <span class="text-sm text-ink-muted">
                            {{ $step->photos->count() === 1 ? 'Subida' : 'Subidas' }} por {{ $uploaders }} ·
                            @if ($step->photos->count() === 1)
                                {{ $lima($first->created_at)?->format('d/m/Y H:i') }}
                            @else
                                {{ $lima($first->created_at)?->format('d/m/Y') }}, de {{ $lima($first->created_at)?->format('H:i') }} a {{ $lima($last->created_at)?->format('H:i') }}
                            @endif
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <p class="m-0 text-sm text-ink-muted">Esta asignación no tiene secciones.</p>
        @endforelse
    </flux:card>

    <template x-teleport="body">
        <div x-show="viewer" x-cloak x-on:keydown.escape.window="viewer = null" class="fixed inset-0 z-50 flex flex-col bg-black/95 text-white" role="dialog" aria-modal="true" aria-label="Foto en grande">
            <div class="flex flex-none items-center justify-end p-3">
                <button type="button" x-on:click="viewer = null" class="inline-flex size-11 items-center justify-center rounded-control text-white" aria-label="Cerrar la foto">
                    <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="flex min-h-0 flex-1 items-center justify-center">
                <img x-bind:src="viewer?.url" alt="Foto de la sección" class="max-h-full max-w-full object-contain">
            </div>
            <p class="m-0 flex-none p-4 pb-6 text-sm">Subida por <strong x-text="viewer?.by"></strong> el <span x-text="viewer?.at"></span>.</p>
        </div>
    </template>
</div>

@script
<script>
    Alpine.data('certificationDetails', () => ({
        open: {},
        viewer: null,
        wide: window.matchMedia('(min-width: 48rem)').matches,

        init() {
            const query = window.matchMedia('(min-width: 48rem)');
            query.addEventListener('change', (event) => this.wide = event.matches);
        },

        isOpen(stepId) {
            return this.open[stepId] === true;
        },

        toggle(stepId) {
            this.open[stepId] = ! this.isOpen(stepId);
        },

        show(url, by, at) {
            this.viewer = { url, by, at };
        },
    }));
</script>
@endscript
