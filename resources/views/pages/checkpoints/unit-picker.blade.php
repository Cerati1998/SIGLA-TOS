<div x-data="checkpointUnits" class="flex flex-col gap-3.5">
    <p class="m-0 text-base text-ink-muted">Solo unidades con la certificación cerrada y el checkpoint sin cerrar.</p>

    <div x-data="photoField('photo', 'thumbnail', () => container() || 'Contenedor')" class="flex flex-col gap-3.5">
        <section class="flex flex-col gap-2 rounded-card bg-white p-4 shadow-card">
            <span id="container-label" class="text-base font-medium">Contenedor</span>
            <div class="flex gap-2" role="group" aria-labelledby="container-label">
                <input type="text" x-model="letters" maxlength="4" autocomplete="off" placeholder="TEMU" aria-label="Letras del contenedor"
                    class="h-13 w-23 flex-none rounded-control border border-control bg-white px-3 text-lg tracking-wide uppercase">
                <input type="text" x-model="numbers" x-digits-only inputmode="numeric" maxlength="7" autocomplete="off" placeholder="9795602" aria-label="Números del contenedor"
                    class="h-13 min-w-0 flex-1 rounded-control border border-control bg-white px-3 text-lg tracking-wide">
                <x-camera-scanner title="Escanear el contenedor" x-on:barcode-detected="scanned" />
            </div>
            <span class="text-sm text-ink-muted">4 letras y 7 números. Escríbelo o escanéalo.</span>

            <input type="file" accept="image/*" capture="environment" x-ref="camera" x-on:change="captured" class="hidden" aria-hidden="true" tabindex="-1">

            <button type="button" x-show="canCapture && ! hasPhoto && ! uploading" x-on:click="take"
                class="inline-flex h-12 w-full items-center justify-center gap-2.5 rounded-control border border-control bg-white px-4 text-base font-medium">
                <x-feather name="camera" class="size-5" />
                Tomar foto del contenedor <span class="text-sm font-normal text-ink-muted">(opcional)</span>
            </button>

            <p x-show="uploading" x-cloak role="status" class="m-0 flex h-12 items-center justify-center gap-2 rounded-control border border-line bg-surface text-sm text-ink-muted">
                <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                Preparando y subiendo la foto…
            </p>

            <div x-show="hasPhoto" x-cloak class="flex items-center gap-3 rounded-card border border-line bg-surface p-2.5">
                <button type="button" x-on:click="openViewer" class="size-16 flex-none cursor-zoom-in overflow-hidden rounded-control" aria-label="Ver la foto del contenedor">
                    <img x-bind:src="thumbnailUrl" alt="" class="size-full object-cover">
                </button>
                <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                    <strong class="text-[15px] font-semibold">Foto del contenedor</strong>
                    <span class="text-[13px] text-ink-muted">Queda como evidencia en el checkpoint de la unidad que elijas. No lee el número.</span>
                </span>
                <flux:button size="sm" x-on:click="remove" class="max-md:h-11">Quitar</flux:button>
            </div>

            <p x-show="failed" x-cloak role="alert" class="m-0 text-sm text-danger">No se pudo preparar o subir la foto. Tómala otra vez.</p>
            <flux:error name="photo" />
        </section>

        <p class="m-0 pt-1 text-sm font-semibold">Con checkpoint pendiente · {{ count($units) }}</p>

        @forelse ($units as $unit)
            <button type="button" wire:key="unit-{{ $unit['id'] }}" wire:click="open({{ $unit['id'] }})" wire:loading.attr="disabled" wire:target="open"
                x-bind:disabled="uploading" x-show="matches(@js($unit['container']))" data-unit
                class="flex w-full flex-col gap-2 rounded-card border border-line bg-white px-4 py-3.5 text-left text-ink shadow-card hover:border-brand disabled:opacity-60">
                <span class="flex items-center justify-between gap-2.5">
                    <span class="font-heading text-lg font-medium tracking-wide text-brand">{{ $unit['container'] }}</span>
                    <x-status-badge :variant="$unit['started'] ? 'info' : 'warning'">{{ $unit['started'] ? 'A medias' : 'Sin empezar' }}</x-status-badge>
                </span>
                <span class="h-1.5 overflow-hidden rounded-full bg-line" role="progressbar" aria-label="Avance del checkpoint" aria-valuemin="0" aria-valuemax="{{ $unit['total'] }}" aria-valuenow="{{ $unit['done'] }}">
                    <span class="block h-full rounded-full bg-logo-orange" style="width: {{ $unit['total'] === 0 ? 0 : round($unit['done'] * 100 / $unit['total']) }}%"></span>
                </span>
                <span class="flex items-center justify-between gap-2.5 text-sm text-ink-muted">
                    <span>{{ $unit['done'] }} de {{ $unit['total'] }} pasos · {{ $unit['pouchCode'] }}</span>
                    <span>{{ $unit['shippingLine'] }}</span>
                </span>
                <span class="text-xs text-ink-muted">
                    @if ($unit['lastChange'] !== null)
                        Último cambio de {{ $unit['lastChange']['by'] }}, {{ $unit['lastChange']['at']->diffForHumans() }}
                    @else
                        Certificación cerrada el {{ $unit['closedAt']->format('d/m/Y') }} a las {{ $unit['closedAt']->format('H:i') }}
                    @endif
                </span>
            </button>
        @empty
            <p class="m-0 rounded-card border-2 border-dashed border-control px-4 py-6 text-center text-ink-muted">
                No hay unidades con el checkpoint pendiente.
            </p>
        @endforelse

        @if ($units !== [])
            <p x-show="container() !== '' && visibleCount() === 0" x-cloak class="m-0 rounded-card border-2 border-dashed border-control px-4 py-6 text-center text-ink-muted">
                Ninguna unidad pendiente tiene ese contenedor.
            </p>
            <p class="m-0 text-xs text-ink-muted">Al tocar una unidad empieza su paso a paso.</p>
        @endif

        <template x-teleport="body">
            <div x-show="viewerOpen" x-cloak role="dialog" aria-modal="true" aria-label="Foto del contenedor" x-on:keydown.escape.window="closeViewer" class="fixed inset-0 z-70 flex flex-col bg-ink text-white">
                <div class="flex h-16 flex-none items-center justify-between px-4">
                    <strong class="font-heading text-lg font-medium">Foto del contenedor</strong>
                    <button type="button" x-on:click="closeViewer" class="inline-flex size-11 items-center justify-center rounded-full bg-white/15" aria-label="Cerrar la foto">
                        <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="flex min-h-0 flex-1 items-center justify-center bg-black p-2">
                    <img x-bind:src="photoUrl" alt="Foto del contenedor" class="max-h-full max-w-full object-contain">
                </div>
                <div class="flex flex-none justify-center gap-2.5 p-4 pb-6">
                    <flux:button x-on:click="take" class="max-md:h-11">Volver a tomar</flux:button>
                    <flux:button x-on:click="remove" class="text-danger! max-md:h-11">Quitar</flux:button>
                    <flux:button variant="primary" x-on:click="closeViewer" class="max-md:h-11">Cerrar</flux:button>
                </div>
            </div>
        </template>
    </div>
</div>

@script
<script>
    Alpine.data('checkpointUnits', () => ({
        letters: '',
        numbers: '',
        canCapture: window.matchMedia('(pointer: coarse)').matches,

        container() {
            return (this.letters.trim() + this.numbers.trim()).toUpperCase();
        },

        matches(container) {
            const text = this.container();

            return text === '' || container.includes(text);
        },

        visibleCount() {
            return [...this.$root.querySelectorAll('[data-unit]')].filter((unit) => unit.style.display !== 'none').length;
        },

        scanned(event) {
            const value = String(event.detail.value).replace(/\s+/g, '').toUpperCase();
            this.letters = value.slice(0, 4);
            this.numbers = value.slice(4, 11);
        },
    }));
</script>
@endscript
