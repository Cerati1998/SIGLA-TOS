@php
    use App\Enums\CertificationSectionType;

    $other = $type === CertificationSectionType::Internal ? 'externa' : 'interna';
@endphp

<div x-data="inspectionUnits" class="flex flex-col gap-3.5">
    <section class="flex flex-col gap-2 rounded-card bg-white p-4 shadow-card">
        <label for="unit-search" class="text-base font-medium">Contenedor o funda</label>
        <div class="flex gap-2">
            <input id="unit-search" type="search" x-model="query" autocomplete="off" placeholder="ABCU1234567 o KIT-000046"
                class="h-13 min-w-0 flex-1 rounded-control border border-control bg-white px-3.5 text-lg tracking-wide uppercase">
            <x-camera-scanner title="Escanear la funda" x-on:barcode-detected="scanned" />
        </div>
    </section>

    <p class="m-0 pt-1 text-sm font-semibold">
        Con la inspección {{ mb_strtolower($type->label()) }} pendiente · {{ count($units) }}
    </p>

    @forelse ($units as $unit)
        <a wire:key="unit-{{ $unit['id'] }}" href="{{ route('intranet.inspections.show', [$type, $unit['id']]) }}" wire:navigate
            x-show="matches(@js($unit['container']), @js($unit['pouchCode']))" data-unit
            class="flex flex-col gap-2 rounded-card border border-line bg-white px-4 py-3.5 text-ink no-underline shadow-card hover:border-brand hover:text-ink">
            <span class="flex items-center justify-between gap-2.5">
                <span class="font-heading text-lg font-medium tracking-wide text-brand">{{ $unit['container'] }}</span>
                @if ($unit['reinspection'])
                    <x-status-badge variant="highlight">Reinspección</x-status-badge>
                @else
                    <x-status-badge :variant="$unit['done'] > 0 ? 'info' : 'warning'">{{ $unit['done'] > 0 ? 'En proceso' : 'Sin empezar' }}</x-status-badge>
                @endif
            </span>
            <span class="h-1.5 overflow-hidden rounded-full bg-line" role="progressbar" aria-label="Avance de la inspección" aria-valuemin="0" aria-valuemax="{{ $unit['total'] }}" aria-valuenow="{{ $unit['done'] }}">
                <span class="block h-full rounded-full bg-logo-orange" style="width: {{ round($unit['done'] * 100 / $unit['total']) }}%"></span>
            </span>
            <span class="flex items-center justify-between gap-2.5 text-sm text-ink-muted">
                <span>{{ $unit['reinspection'] ? ($unit['total'] === 1 ? 'Sección reabierta' : 'Secciones reabiertas') : $type->label() }} {{ $unit['done'] }} de {{ $unit['total'] }} · {{ $unit['pouchCode'] }}</span>
                <span>{{ $unit['shippingLine'] }}</span>
            </span>
            <span class="text-xs text-ink-muted">
                @if ($unit['lastPhoto'] !== null)
                    Última foto de {{ $unit['lastPhoto']->uploader->name }}, {{ $unit['lastPhoto']->created_at->diffForHumans() }}
                @elseif ($unit['otherTotal'] > 0 && $unit['otherDone'] === $unit['otherTotal'])
                    La {{ $other }} ya está completa
                @elseif ($unit['otherDone'] > 0)
                    La {{ $other }} está en curso
                @else
                    Asignada el {{ $unit['startedAt']->format('d/m/Y') }} a las {{ $unit['startedAt']->format('H:i') }}
                @endif
            </span>
        </a>
    @empty
        <p class="m-0 rounded-card border-2 border-dashed border-control px-4 py-6 text-center text-ink-muted">
            No hay unidades con la inspección {{ mb_strtolower($type->label()) }} pendiente.
        </p>
    @endforelse

    @if ($units !== [])
        <p x-show="query !== '' && visibleCount() === 0" x-cloak class="m-0 rounded-card border-2 border-dashed border-control px-4 py-6 text-center text-ink-muted">
            Ninguna unidad pendiente tiene ese contenedor o esa funda.
        </p>
    @endif
</div>

@script
<script>
    Alpine.data('inspectionUnits', () => ({
        query: '',

        matches(container, pouchCode) {
            const text = this.query.trim().toUpperCase();

            return text === '' || container.includes(text) || pouchCode.toUpperCase().includes(text);
        },

        visibleCount() {
            return [...this.$root.querySelectorAll('[data-unit]')].filter((unit) => unit.style.display !== 'none').length;
        },

        scanned(event) {
            this.query = event.detail.value;

            this.$nextTick(() => {
                const visible = [...this.$root.querySelectorAll('[data-unit]')].filter((unit) => unit.style.display !== 'none');

                if (visible.length === 1) {
                    Livewire.navigate(visible[0].getAttribute('href'));
                }
            });
        },
    }));
</script>
@endscript
