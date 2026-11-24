@php
    $steps = $this->steps;
    $extraPhotos = $this->extraPhotos;
    $deviceSections = $this->deviceSections;
    $container = $checkpoint->certification->container;
    $closed = $checkpoint->isClosed();
    $total = $steps->count();
    $done = $steps->filter(fn ($step) => $step->isComplete())->count();
    $firstPending = $steps->search(fn ($step) => ! $step->isComplete());
    $start = $closed ? 0 : ($firstPending === false ? $total : $firstPending);
    $photoSteps = $steps->reject(fn ($step) => $step->isVerification());
    $marks = $steps->flatMap(fn ($step) => $step->marks);
    $incorrect = $marks->filter(fn ($mark) => $mark->is_correct === false);
    $complete = $done === $total;
    $canFinish = ! $closed && $complete && $incorrect->isEmpty();
@endphp

<div x-data="checkpointSteps({ start: {{ $start }}, total: {{ $total + 1 }}, container: @js($container), closed: @js($closed) })"
    class="flex flex-col gap-4 max-md:pb-28">

    <input type="file" accept="image/*" capture="environment" x-ref="camera" x-on:change="captured" class="hidden" aria-hidden="true" tabindex="-1">

    @if ($closed)
        <p class="m-0 flex items-start gap-2.5 rounded-control bg-badge-success p-3.5 text-sm text-badge-success-ink">
            <x-feather name="lock" class="mt-0.5 size-4 shrink-0" />
            <span>Checkpoint cerrado por {{ $checkpoint->closer?->name }} el {{ $checkpoint->closed_at?->format('d/m/Y') }} a las {{ $checkpoint->closed_at?->format('H:i') }}: solo lectura.</span>
        </p>
    @endif

    <p x-show="! canCapture && ! closed" x-cloak class="m-0 flex items-start gap-2.5 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
        <x-feather name="smartphone" class="mt-0.5 size-4 shrink-0" />
        <span>Las fotos se toman desde el teléfono: aquí se ven en solo lectura. Las series sí se pueden marcar.</span>
    </p>

    <div class="flex flex-col gap-2.5">
        <div class="flex items-baseline justify-between gap-2 text-sm">
            <span class="text-ink-muted">
                <strong class="text-brand">Checkpoint</strong> · {{ $container }} ·
                <span x-show="current < {{ $total }}">Paso <span x-text="current + 1"></span> de {{ $total }}</span>
                <span x-show="current === {{ $total }}" x-cloak>Para finalizar</span>
            </span>
            <span class="font-semibold">{{ $done }} {{ $done === 1 ? 'completo' : 'completos' }}</span>
        </div>
        <div class="h-2 overflow-hidden rounded-full bg-line" role="progressbar" aria-label="Pasos del checkpoint completos" aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $done }}">
            <div @class(['h-full rounded-full', 'bg-status-green' => $complete, 'bg-logo-orange' => ! $complete]) style="width: {{ $total === 0 ? 0 : round($done * 100 / $total) }}%"></div>
        </div>
    </div>

    <div class="grid items-start gap-5 md:grid-cols-[18.75rem_minmax(0,1fr)]">
        <nav aria-label="Pasos" class="flex flex-col rounded-card bg-white py-1 shadow-card max-md:hidden">
            @foreach ($steps as $index => $step)
                <button type="button" wire:key="nav-{{ $step->id }}" x-on:click="go({{ $index }})"
                    x-bind:class="current === {{ $index }} ? 'border-logo-orange bg-table-head font-semibold text-brand' : 'border-transparent'"
                    class="flex min-h-11 items-center gap-2.5 border-l-4 px-3 py-1.5 text-left text-sm">
                    <span class="w-5 flex-none text-ink-muted">{{ $index + 1 }}</span>
                    <span class="min-w-0 flex-1">{{ $step->section->name }}</span>
                    <span @class(['size-2.5 flex-none rounded-full', 'bg-status-green' => $step->isComplete(), 'border-2 border-highlight' => ! $step->isComplete()])
                        aria-label="{{ $step->isComplete() ? 'Completo' : 'Sin completar' }}"></span>
                </button>
            @endforeach
            <button type="button" x-on:click="go({{ $total }})"
                x-bind:class="current === {{ $total }} ? 'border-logo-orange bg-table-head font-semibold text-brand' : 'border-transparent'"
                class="flex min-h-11 items-center gap-2.5 border-l-4 px-3 py-1.5 text-left text-sm">
                <span class="w-5 flex-none text-ink-muted"><i class="fa-solid fa-flag-checkered text-xs" aria-hidden="true"></i></span>
                <span class="min-w-0 flex-1">Observaciones y finalizar</span>
            </button>
        </nav>

        <div class="flex min-w-0 flex-col gap-4">
            @foreach ($steps as $index => $step)
                <section wire:key="step-{{ $step->id }}" x-show="current === {{ $index }}" @if ($index !== $start) x-cloak @endif class="flex flex-col gap-4">
                    @if ($step->isVerification())
                        @include('pages.checkpoints.partials.verification-step')
                    @else
                        @include('pages.checkpoints.partials.photo-step')
                    @endif
                </section>
            @endforeach

            <section wire:key="final-step" x-show="current === {{ $total }}" @if ($start !== $total) x-cloak @endif class="flex flex-col gap-4">
                @include('pages.checkpoints.partials.final-step')
            </section>

            <button type="button" x-on:click="listOpen = true" class="flex min-h-12 w-full items-center justify-between rounded-control border border-line bg-white px-3.5 text-base font-medium text-brand md:hidden">
                Ir a otro paso
                <i class="fa-solid fa-chevron-right text-sm" aria-hidden="true"></i>
            </button>

            <div class="flex justify-between gap-3 border-t border-line pt-4 max-md:hidden">
                <flux:button x-on:click="previous" x-bind:disabled="current === 0" icon="chevron-left">Atrás</flux:button>
                <flux:button variant="primary" x-show="current < {{ $total }}" x-on:click="next" icon:trailing="chevron-right">Siguiente paso</flux:button>
                @unless ($closed)
                    <flux:button variant="primary" x-show="current === {{ $total }}" x-cloak wire:click="finish" wire:loading.attr="disabled" wire:target="finish" :disabled="! $canFinish">Finalizar y cerrar</flux:button>
                @endunless
            </div>
        </div>
    </div>

    <div class="fixed inset-x-0 bottom-0 z-20 grid grid-cols-[1fr_2fr] gap-2.5 border-t border-line bg-white px-4 pt-3 pb-4 shadow-[0_-3px_8px_rgb(0_0_0/0.06)] md:hidden">
        <button type="button" x-on:click="previous" x-bind:disabled="current === 0" class="inline-flex h-13 items-center justify-center gap-2 rounded-control border border-control bg-white text-base font-medium disabled:opacity-50">
            <i class="fa-solid fa-chevron-left text-sm" aria-hidden="true"></i> Atrás
        </button>
        <button type="button" x-show="current < {{ $total }}" x-on:click="next" class="inline-flex h-13 items-center justify-center gap-2 rounded-control bg-brand text-base font-medium text-white">
            Siguiente paso <i class="fa-solid fa-chevron-right text-sm" aria-hidden="true"></i>
        </button>
        @unless ($closed)
            <button type="button" x-show="current === {{ $total }}" x-cloak wire:click="finish" wire:loading.attr="disabled" wire:target="finish" @disabled(! $canFinish)
                class="inline-flex h-13 items-center justify-center gap-2 rounded-control bg-brand text-base font-medium text-white disabled:opacity-50">
                Finalizar y cerrar
            </button>
        @endunless
    </div>

    <template x-teleport="body">
        <div x-show="listOpen" x-cloak x-on:keydown.escape.window="listOpen = false" class="fixed inset-0 z-50 flex flex-col justify-end bg-ink/50" x-on:click.self="listOpen = false">
            <div role="dialog" aria-modal="true" aria-label="Pasos" class="max-h-[80vh] overflow-y-auto rounded-t-card bg-white px-4 pt-3 pb-6">
                <div class="flex items-center justify-between pb-2">
                    <strong class="font-heading text-lg font-medium">Pasos</strong>
                    <button type="button" x-on:click="listOpen = false" class="inline-flex size-11 items-center justify-center" aria-label="Cerrar"><i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i></button>
                </div>
                @foreach ($steps as $index => $step)
                    <button type="button" wire:key="sheet-{{ $step->id }}" x-on:click="go({{ $index }}); listOpen = false"
                        x-bind:class="current === {{ $index }} && 'font-semibold text-brand'"
                        class="flex min-h-13 w-full items-center gap-2.5 border-t border-line py-1.5 text-left text-base">
                        <span class="w-5.5 flex-none text-ink-muted">{{ $index + 1 }}</span>
                        <span class="min-w-0 flex-1">{{ $step->section->name }}</span>
                        @if ($step->isComplete())
                            <x-status-badge variant="success">Completo</x-status-badge>
                        @else
                            <x-status-badge variant="warning">{{ $step->isVerification() ? 'Falta marcar' : 'Sin foto' }}</x-status-badge>
                        @endif
                    </button>
                @endforeach
                <button type="button" x-on:click="go({{ $total }}); listOpen = false"
                    x-bind:class="current === {{ $total }} && 'font-semibold text-brand'"
                    class="flex min-h-13 w-full items-center gap-2.5 border-t border-line py-1.5 text-left text-base">
                    <span class="w-5.5 flex-none text-ink-muted"><i class="fa-solid fa-flag-checkered text-xs" aria-hidden="true"></i></span>
                    <span class="min-w-0 flex-1">Observaciones y finalizar</span>
                </button>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="viewer !== null" x-cloak x-on:keydown.escape.window="viewer = null" class="fixed inset-0 z-50 flex flex-col bg-ink text-white">
            <div class="flex h-16 flex-none items-center justify-between px-4">
                <strong class="font-heading text-lg font-medium" x-text="viewer?.section"></strong>
                <button type="button" x-on:click="viewer = null" class="inline-flex size-11 items-center justify-center rounded-full bg-white/15" aria-label="Cerrar la foto">
                    <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="flex min-h-0 flex-1 items-center justify-center bg-black">
                <img x-bind:src="viewer?.url" alt="Foto del checkpoint" class="max-h-full max-w-full object-contain">
            </div>
            <div class="flex flex-none flex-col gap-3 p-4 pb-6">
                <span class="text-sm">Subida por <strong x-text="viewer?.by"></strong> el <span x-text="viewer?.at"></span>.</span>
                <div x-show="canCapture" class="grid grid-cols-2 gap-2.5">
                    <button type="button" x-on:click="replace" class="inline-flex h-13 items-center justify-center rounded-control bg-white text-base font-medium text-brand">Reemplazar</button>
                    <button type="button" x-on:click="remove" wire:loading.attr="disabled" wire:target="deletePhoto" class="inline-flex h-13 items-center justify-center rounded-control border border-white text-base font-medium text-white">Borrar</button>
                </div>
            </div>
        </div>
    </template>
</div>

@script
<script>
    Alpine.data('checkpointSteps', ({ start, total, container, closed }) => ({
        ...window.photoUploads(container),
        current: start,
        total,
        closed,
        canCapture: ! closed && window.matchMedia('(pointer: coarse)').matches,
        listOpen: false,
        viewer: null,

        go(index) {
            this.current = index;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        previous() {
            if (this.current > 0) {
                this.go(this.current - 1);
            }
        },

        next() {
            if (this.current < this.total - 1) {
                this.go(this.current + 1);
            }
        },

        openViewer(photo) {
            this.viewer = photo;
        },

        replace() {
            const { stepId, photoId } = this.viewer;
            this.viewer = null;
            this.takePhoto(stepId, photoId);
        },

        remove() {
            const { stepId, photoId, section } = this.viewer;

            window.confirmAction('¿Borrar esta foto?', `Se quita de «${section}». No se puede deshacer.`, () => {
                this.viewer = null;
                this.$wire.deletePhoto(stepId, photoId);
            });
        },
    }));
</script>
@endscript
