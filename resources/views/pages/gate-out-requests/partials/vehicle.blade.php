{{-- A vehicle of the request, with a side view of a tractor or a flatbed semi-trailer as reference. --}}
<article class="flex flex-col overflow-hidden rounded-card border border-line">
    <div class="flex items-center gap-3 border-b border-line bg-badge-info p-3 md:gap-4 md:px-4 md:py-3.5">
        @if ($kind === 'tractor')
            <svg viewBox="0 0 132 66" class="h-13 w-26 flex-none md:h-16.5 md:w-33" role="img" aria-label="Figura de un remolcador visto de lado">
                <path d="M6 46h120" class="stroke-control" stroke-width="2" stroke-linecap="round" />
                <path d="M14 40V14a4 4 0 0 1 4-4h22l12 14h8a4 4 0 0 1 4 4v12" class="fill-white stroke-brand" stroke-width="2.5" stroke-linejoin="round" />
                <path d="M22 16h15l8 9H22z" class="fill-indigo-200 stroke-brand" stroke-width="2" stroke-linejoin="round" />
                <path d="M30 10V4" class="stroke-brand" stroke-width="2.5" stroke-linecap="round" />
                <path d="M10 40h112" class="stroke-brand" stroke-width="3" stroke-linecap="round" />
                <rect x="88" y="33" width="20" height="5" rx="1.5" class="fill-highlight" />
                @foreach ([30, 92, 111] as $wheel)
                    <circle cx="{{ $wheel }}" cy="46" r="8" class="fill-ink" />
                    <circle cx="{{ $wheel }}" cy="46" r="3" class="fill-white" />
                @endforeach
            </svg>
        @else
            <svg viewBox="0 0 132 66" class="h-13 w-26 flex-none md:h-16.5 md:w-33" role="img" aria-label="Figura de un semirremolque visto de lado">
                <path d="M6 54h120" class="stroke-control" stroke-width="2" stroke-linecap="round" />
                <rect x="12" y="8" width="108" height="26" rx="2" fill="none" class="stroke-control" stroke-width="1.5" stroke-dasharray="5 4" />
                <path d="M8 36h118v6H8z" class="fill-white stroke-brand" stroke-width="2.5" stroke-linejoin="round" />
                <path d="M8 36v-8" class="stroke-brand" stroke-width="2.5" stroke-linecap="round" />
                <path d="M34 42v10M30 52h8" class="stroke-brand" stroke-width="2.5" stroke-linecap="round" />
                <rect x="12" y="42" width="6" height="4" class="fill-highlight" />
                @foreach ([94, 113] as $wheel)
                    <circle cx="{{ $wheel }}" cy="50" r="8" class="fill-ink" />
                    <circle cx="{{ $wheel }}" cy="50" r="3" class="fill-white" />
                @endforeach
            </svg>
        @endif

        <div class="flex min-w-0 flex-col gap-1.5">
            <strong class="font-heading text-[1.0625rem] font-medium text-brand md:text-lg">{{ $title }}</strong>
            <span class="inline-flex flex-col items-center self-start overflow-hidden rounded-control border-2 border-ink bg-white" aria-label="Placa {{ $vehicle['plate'] ?? 'sin dato' }}">
                <span class="w-full bg-brand text-center text-[0.5625rem] leading-snug tracking-[0.12em] text-white" aria-hidden="true">PERÚ</span>
                <span class="px-3 pt-px pb-0.5 font-heading text-lg font-medium tracking-widest md:text-xl" aria-hidden="true">{{ $vehicle['plate'] ?? '—' }}</span>
            </span>
        </div>
    </div>

    <dl class="m-0 grid grid-cols-2 gap-3 p-3 md:grid-cols-3 md:p-4">
        @foreach (['Carrocería' => $vehicle['body'], 'Categoría' => $vehicle['category'], 'Marca' => $vehicle['brand']] as $label => $detail)
            <div @class(['flex min-w-0 flex-col gap-0.5', 'max-md:col-span-full' => $label === 'Marca'])>
                <dt class="text-xs tracking-wider text-ink-muted uppercase">{{ $label }}</dt>
                <dd class="m-0 break-words">{{ $detail ?? '—' }}</dd>
            </div>
        @endforeach
    </dl>
</article>
