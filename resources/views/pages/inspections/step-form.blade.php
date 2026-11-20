@php
    $steps = $this->steps;
    $total = $steps->count();
    $done = $steps->filter(fn ($step) => $step->photos->isNotEmpty())->count();
    $firstPending = $steps->search(fn ($step) => $step->photos->isEmpty());
    $tone = $type === \App\Enums\CertificationSectionType::Internal ? 'purple' : 'info';
@endphp

<div x-data="inspectionSteps({ start: {{ $firstPending === false ? 0 : $firstPending }}, total: {{ $total }}, container: @js($certification->container), closed: @js($certification->isReadOnly()) })"
    class="flex flex-col gap-4 max-md:pb-28">

    <input type="file" accept="image/*" capture="environment" x-ref="camera" x-on:change="captured" class="hidden" aria-hidden="true" tabindex="-1">

    @if ($certification->isAnnulled())
        <p class="m-0 flex items-start gap-2.5 rounded-control bg-badge-danger p-3.5 text-sm text-badge-danger-ink">
            <x-feather name="slash" class="mt-0.5 size-4 shrink-0" />
            <span>Asignación anulada: solo lectura.</span>
        </p>
    @elseif ($certification->isClosed())
        <p class="m-0 flex items-start gap-2.5 rounded-control bg-badge-success p-3.5 text-sm text-badge-success-ink">
            <x-feather name="lock" class="mt-0.5 size-4 shrink-0" />
            <span>Certificación cerrada: solo lectura. Para corregir sus fotos, reapertúrala desde Asignaciones.</span>
        </p>
    @endif

    <p x-show="! canCapture && ! closed" x-cloak class="m-0 flex items-start gap-2.5 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
        <x-feather name="smartphone" class="mt-0.5 size-4 shrink-0" />
        <span>Solo lectura: las fotos se toman desde el teléfono. Aquí puedes revisar el avance de cada sección.</span>
    </p>

    <div class="flex flex-col gap-2.5">
        <div class="flex items-baseline justify-between gap-2 text-sm">
            <span class="text-ink-muted">
                <strong class="{{ $tone === 'purple' ? 'text-badge-purple-ink' : 'text-badge-info-ink' }}">Inspección {{ mb_strtolower($type->label()) }}</strong>
                · {{ $certification->container }} · Sección <span x-text="current + 1"></span> de {{ $total }}
            </span>
            <span class="font-semibold">{{ $done }} con foto</span>
        </div>
        <div class="h-2 overflow-hidden rounded-full bg-line" role="progressbar" aria-label="Secciones con foto" aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $done }}">
            <div class="h-full rounded-full bg-logo-orange" style="width: {{ $total === 0 ? 0 : round($done * 100 / $total) }}%"></div>
        </div>
    </div>

    <div class="grid items-start gap-5 md:grid-cols-[18.75rem_minmax(0,1fr)]">
        <nav aria-label="Secciones" class="flex flex-col rounded-card bg-white py-1 shadow-card max-md:hidden">
            @foreach ($steps as $index => $step)
                <button type="button" wire:key="nav-{{ $step->id }}" x-on:click="go({{ $index }})"
                    x-bind:class="current === {{ $index }} ? 'border-logo-orange bg-table-head font-semibold text-brand' : 'border-transparent'"
                    class="flex min-h-11 items-center gap-2.5 border-l-4 px-3 py-1.5 text-left text-sm">
                    <span class="w-5 flex-none text-ink-muted">{{ $index + 1 }}</span>
                    <span class="min-w-0 flex-1">{{ $step->section->name }}</span>
                    <span @class(['size-2.5 flex-none rounded-full', 'bg-status-green' => $step->photos->isNotEmpty(), 'border-2 border-highlight' => $step->photos->isEmpty()])
                        aria-label="{{ $step->photos->isNotEmpty() ? 'Con foto' : 'Sin foto' }}"></span>
                </button>
            @endforeach
        </nav>

        <div class="flex min-w-0 flex-col gap-4">
            @foreach ($steps as $index => $step)
                <section wire:key="step-{{ $step->id }}" x-show="current === {{ $index }}" @if ($index !== ($firstPending === false ? 0 : $firstPending)) x-cloak @endif class="flex flex-col gap-4">
                    <div class="flex flex-col gap-2">
                        <h3 class="m-0 font-heading text-h3 font-medium text-brand">{{ $step->section->name }}</h3>
                        <div class="flex flex-wrap gap-2">
                            <x-status-badge :variant="$tone">{{ $type->label() }}</x-status-badge>
                            @if ($step->photos->isNotEmpty())
                                <x-status-badge variant="success">Con foto</x-status-badge>
                            @else
                                <x-status-badge variant="warning">Sin foto todavía</x-status-badge>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col rounded-card bg-white px-4 py-3.5 shadow-card">
                        <strong class="pb-1.5 font-heading text-base font-medium">Dispositivos de esta sección</strong>
                        @forelse ($step->devices as $device)
                            <div wire:key="device-{{ $device->id }}" class="flex items-center justify-between gap-2.5 border-t border-line py-2.5 text-sm first-of-type:border-t-0">
                                <span>{{ $device->lot->announcement->securityDeviceType->name }}</span>
                                <span class="inline-flex h-7 items-center rounded-full border border-line bg-surface px-2.5 font-medium">{{ $device->serial_number }}</span>
                            </div>
                        @empty
                            <p class="m-0 text-sm text-ink-muted">Solo foto: esta sección no lleva dispositivos.</p>
                        @endforelse
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
                        <p x-show="canCapture" class="m-0 text-sm text-ink-muted">Se abre la cámara del teléfono. Al volver, la foto se sube sola y puedes tomar la siguiente.</p>

                        <div class="grid grid-cols-3 gap-2 md:grid-cols-5">
                            @foreach ($step->photos as $photo)
                                <button type="button" wire:key="photo-{{ $photo->id }}"
                                    x-on:click="openViewer({ stepId: {{ $step->id }}, photoId: {{ $photo->id }}, section: @js($step->section->name), url: @js(route('intranet.certification-photos.show', $photo)), by: @js($photo->uploader->name), at: @js($photo->created_at->format('d/m/Y').' a las '.$photo->created_at->format('H:i')) })"
                                    class="relative aspect-square overflow-hidden rounded-lg bg-control" aria-label="Ver la foto {{ $loop->iteration }}">
                                    <img src="{{ route('intranet.certification-photos.thumbnail', $photo) }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover">
                                    <span class="absolute top-1.5 right-1.5 flex size-5.5 items-center justify-center rounded-full bg-status-green text-white" aria-hidden="true">
                                        <i class="fa-solid fa-check text-[0.625rem]"></i>
                                    </span>
                                </button>
                            @endforeach

                            <div wire:ignore class="contents">
                                <template x-for="item in pendingOf({{ $step->id }})" x-bind:key="item.key">
                                    <button type="button" x-on:click="retry(item)" class="relative aspect-square overflow-hidden rounded-lg bg-control"
                                        x-bind:class="item.status === 'failed' && 'ring-2 ring-danger'" x-bind:aria-label="label(item)">
                                        <img x-bind:src="item.url" alt="" class="absolute inset-0 size-full object-cover">
                                        <span class="absolute inset-0 flex flex-col items-center justify-center gap-1.5 p-1.5 text-center text-xs font-medium text-white"
                                            x-bind:class="item.status === 'failed' ? 'bg-badge-danger-ink/75' : 'bg-ink/55'">
                                            <span x-text="label(item)"></span>
                                            <span x-show="item.status === 'uploading'" class="h-1.25 w-[70%] overflow-hidden rounded-full bg-white/30">
                                                <span class="block h-full bg-white" x-bind:style="`width: ${item.progress}%`"></span>
                                            </span>
                                        </span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </section>
            @endforeach

            <button type="button" x-on:click="listOpen = true" class="flex min-h-12 w-full items-center justify-between rounded-control border border-line bg-white px-3.5 text-base font-medium text-brand md:hidden">
                Ir a otra sección
                <i class="fa-solid fa-chevron-right text-sm" aria-hidden="true"></i>
            </button>

            <div class="flex justify-between gap-3 border-t border-line pt-4 max-md:hidden">
                <flux:button x-on:click="previous" x-bind:disabled="current === 0" icon="chevron-left">Atrás</flux:button>
                <flux:button variant="primary" x-on:click="next" x-bind:disabled="current === total - 1" icon:trailing="chevron-right">Siguiente sección</flux:button>
            </div>
        </div>
    </div>

    <div class="fixed inset-x-0 bottom-0 z-20 grid grid-cols-[1fr_2fr] gap-2.5 border-t border-line bg-white px-4 pt-3 pb-4 shadow-[0_-3px_8px_rgb(0_0_0/0.06)] md:hidden">
        <button type="button" x-on:click="previous" x-bind:disabled="current === 0" class="inline-flex h-13 items-center justify-center gap-2 rounded-control border border-control bg-white text-base font-medium disabled:opacity-50">
            <i class="fa-solid fa-chevron-left text-sm" aria-hidden="true"></i> Atrás
        </button>
        <button type="button" x-on:click="next" x-bind:disabled="current === total - 1" class="inline-flex h-13 items-center justify-center gap-2 rounded-control bg-brand text-base font-medium text-white disabled:opacity-50">
            Siguiente sección <i class="fa-solid fa-chevron-right text-sm" aria-hidden="true"></i>
        </button>
    </div>

    <template x-teleport="body">
        <div x-show="listOpen" x-cloak x-on:keydown.escape.window="listOpen = false" class="fixed inset-0 z-50 flex flex-col justify-end bg-ink/50" x-on:click.self="listOpen = false">
            <div role="dialog" aria-modal="true" aria-label="Secciones" class="max-h-[80vh] overflow-y-auto rounded-t-card bg-white px-4 pt-3 pb-6">
                <div class="flex items-center justify-between pb-2">
                    <strong class="font-heading text-lg font-medium">Secciones</strong>
                    <button type="button" x-on:click="listOpen = false" class="inline-flex size-11 items-center justify-center" aria-label="Cerrar"><i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i></button>
                </div>
                @foreach ($steps as $index => $step)
                    <button type="button" wire:key="sheet-{{ $step->id }}" x-on:click="go({{ $index }}); listOpen = false"
                        x-bind:class="current === {{ $index }} && 'font-semibold text-brand'"
                        class="flex min-h-13 w-full items-center gap-2.5 border-t border-line py-1.5 text-left text-base">
                        <span class="w-5.5 flex-none text-ink-muted">{{ $index + 1 }}</span>
                        <span class="min-w-0 flex-1">{{ $step->section->name }}</span>
                        @if ($step->photos->isNotEmpty())
                            <x-status-badge variant="success">{{ trans_choice(':count foto|:count fotos', $step->photos->count()) }}</x-status-badge>
                        @else
                            <x-status-badge variant="warning">Sin foto</x-status-badge>
                        @endif
                    </button>
                @endforeach
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
                <img x-bind:src="viewer?.url" alt="Foto de la sección" class="max-h-full max-w-full object-contain">
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
    Alpine.data('inspectionSteps', ({ start, total, container, closed }) => ({
        current: start,
        total,
        container,
        closed,
        canCapture: ! closed && window.matchMedia('(pointer: coarse)').matches,
        listOpen: false,
        viewer: null,
        target: null,
        pending: {},
        queue: [],
        uploading: false,

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

        pendingOf(stepId) {
            return this.pending[stepId] ?? [];
        },

        label(item) {
            return {
                preparing: 'Preparando…',
                uploading: `Subiendo ${item.progress} %`,
                failed: 'No se subió · Reintentar',
            }[item.status];
        },

        takePhoto(stepId, replacedPhotoId = null) {
            this.target = { stepId, replacedPhotoId };
            this.$refs.camera.click();
        },

        watermark() {
            const now = new Date();
            const pad = (value) => String(value).padStart(2, '0');

            return `${pad(now.getDate())}/${pad(now.getMonth() + 1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())} · ${this.container}`;
        },

        async captured(event) {
            const file = event.target.files[0];
            event.target.value = '';

            if (! file || this.target === null) {
                return;
            }

            const { stepId, replacedPhotoId } = this.target;
            this.target = null;
            this.pending[stepId] = [...this.pendingOf(stepId), {
                key: `${Date.now()}-${Math.random()}`, stepId, replacedPhotoId, status: 'preparing', progress: 0, url: URL.createObjectURL(file), blobs: null,
            }];
            const item = this.pending[stepId].at(-1);

            try {
                item.blobs = await window.preparePhoto(file, { watermark: this.watermark() });
                URL.revokeObjectURL(item.url);
                item.url = URL.createObjectURL(item.blobs.thumbnail);
                this.queue.push(item);
                this.upload();
            } catch {
                item.status = 'failed';
            }
        },

        retry(item) {
            if (item.status !== 'failed' || item.blobs === null) {
                return;
            }

            this.queue.push(item);
            this.upload();
        },

        send(property, blob, item) {
            const file = new File([blob], 'foto.jpg', { type: 'image/jpeg' });

            return new Promise((resolve, reject) => {
                this.$wire.upload(property, file, resolve, reject, (event) => {
                    if (property === 'photo') {
                        item.progress = event.detail.progress;
                    }
                });
            });
        },

        async upload() {
            if (this.uploading) {
                return;
            }

            this.uploading = true;

            while (this.queue.length > 0) {
                const item = this.queue.shift();
                item.status = 'uploading';
                item.progress = 0;

                try {
                    await this.send('photo', item.blobs.photo, item);
                    await this.send('thumbnail', item.blobs.thumbnail, item);

                    if (await this.$wire.savePhoto(item.stepId, item.replacedPhotoId) !== true) {
                        throw new Error('not saved');
                    }

                    URL.revokeObjectURL(item.url);
                    this.pending[item.stepId] = this.pendingOf(item.stepId).filter((pending) => pending.key !== item.key);
                } catch {
                    item.status = 'failed';
                }
            }

            this.uploading = false;
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

            window.confirmAction('¿Borrar esta foto?', `Se quita de la sección «${section}». No se puede deshacer.`, () => {
                this.viewer = null;
                this.$wire.deletePhoto(stepId, photoId);
            });
        },
    }));
</script>
@endscript
