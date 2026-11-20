@php
    $cards = [
        [
            'title' => 'Asignación',
            'text' => 'Vincula una solicitud de retiro con su kit.',
            'icon' => 'link',
            'tone' => 'bg-table-head text-brand',
            'route' => route('intranet.certifications.create'),
            'count' => null,
        ],
        [
            'title' => 'Inspección interna',
            'text' => 'Fotos de las secciones internas del contenedor.',
            'icon' => 'box',
            'tone' => 'bg-badge-purple text-badge-purple-ink',
            'route' => route('intranet.inspections.index', 'internal'),
            'count' => $pending['internal'],
        ],
        [
            'title' => 'Inspección externa',
            'text' => 'Fotos de las secciones externas del contenedor.',
            'icon' => 'truck',
            'tone' => 'bg-badge-info text-badge-info-ink',
            'route' => route('intranet.inspections.index', 'external'),
            'count' => $pending['external'],
        ],
    ];
@endphp

<x-layouts::app title="Certificaciones" :breadcrumbs="[
    ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
    ['name' => 'Certificaciones'],
]">
    <div class="flex flex-col gap-4">
        <p class="m-0 text-base text-ink-muted">Primero se asigna el kit. Después, la inspección interna y la externa se hacen en cualquier orden.</p>

        <div class="grid gap-4 md:grid-cols-3">
            @foreach ($cards as $card)
                <a href="{{ $card['route'] }}" wire:navigate class="flex items-center gap-4 rounded-card border border-line bg-white p-4 text-ink no-underline shadow-card hover:border-brand hover:text-ink md:flex-col md:items-start md:p-5.25">
                    <span class="flex size-14 flex-none items-center justify-center rounded-xl {{ $card['tone'] }}">
                        <x-feather :name="$card['icon']" class="size-7" />
                    </span>
                    <span class="flex min-w-0 flex-1 flex-col gap-1">
                        <strong class="font-heading text-lg font-medium text-brand">{{ $card['title'] }}</strong>
                        <span class="text-sm text-ink-muted">{{ $card['text'] }}</span>
                        @if ($card['count'] !== null)
                            <span class="self-start">
                                <x-status-badge :variant="$card['count'] > 0 ? 'warning' : 'success'">
                                    {{ $card['count'] === 0 ? 'Sin pendientes' : trans_choice(':count pendiente|:count pendientes', $card['count']) }}
                                </x-status-badge>
                            </span>
                        @endif
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</x-layouts::app>
