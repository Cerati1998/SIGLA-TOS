@php
    $missingText = collect($missingSlots)
        ->map(fn (array $kitSlot): string => ($kitSlot['quantity'] - $kitSlot['added']).' '.\App\Actions\Kits\AddSecurityDeviceToKit::slotLabel($kitSlot))
        ->join(', ', ' y ');
    $isComplete = $missingSlots === [];
    $percent = $totalQuantity === 0 ? 0 : (int) round($totalAdded * 100 / $totalQuantity);
@endphp

<div x-data="kitScanner" class="flex flex-col gap-5.25">
    <flux:card class="flex flex-col gap-4 p-5.25!">
        <x-panel.form-step number="1" title="Datos del kit" text="Guardados. La funda, el tipo de kit y la naviera ya no cambian." />

        <dl class="m-0 grid grid-cols-2 gap-4 md:grid-cols-5">
            <div class="flex min-w-0 flex-col gap-0.5">
                <dt class="text-xs tracking-wider text-ink-muted uppercase">Funda</dt>
                <dd class="m-0 break-words"><strong>{{ $kit->pouch_code }}</strong></dd>
            </div>
            <div class="flex min-w-0 flex-col gap-0.5">
                <dt class="text-xs tracking-wider text-ink-muted uppercase">Tipo de kit</dt>
                <dd class="m-0 break-words">{{ $kit->kitType->name }}</dd>
            </div>
            <div class="flex min-w-0 flex-col gap-0.5">
                <dt class="text-xs tracking-wider text-ink-muted uppercase">Línea naviera</dt>
                <dd class="m-0 break-words">{{ $kit->shippingLine->code }} · {{ $kit->shippingLine->name }}</dd>
            </div>
            <div class="flex min-w-0 flex-col gap-0.5">
                <dt class="text-xs tracking-wider text-ink-muted uppercase">Semana</dt>
                <dd class="m-0">{{ $kit->week() }}</dd>
            </div>
            <div class="flex min-w-0 flex-col gap-0.5">
                <dt class="text-xs tracking-wider text-ink-muted uppercase">Estado</dt>
                <dd class="m-0">@include('pages.kits.partials.status')</dd>
            </div>
        </dl>
    </flux:card>

    <flux:card class="flex flex-col gap-4 p-5.25!">
        <x-panel.form-step
            number="2"
            title="Dispositivos del kit"
            :text="$canChange
                ? 'Un campo por cada tipo que lleva el kit. Escanea o escribe la serie en el campo de su tipo y pulsa Enter: se agrega sola, sin botón de guardar. Al completar un tipo, el cursor pasa solo al siguiente.'
                : 'Las series que lleva el kit. Ya no se agregan ni se quitan.'"
        />

        @if ($kitDispatch !== null)
            <div role="status" class="flex flex-wrap items-center justify-between gap-3 rounded-control bg-badge-info p-3.5 text-sm leading-normal text-badge-info-ink">
                <span class="flex gap-2.5">
                    <x-feather name="send" class="mt-0.5 size-4.5" />
                    <span>
                        <strong>Despachado en {{ $kitDispatch->number() }}</strong>
                        a {{ $kitDispatch->receiver->full_name }} el {{ ($kitDispatch->created_at ?? now())->timezone('America/Lima')->format('d/m/Y H:i') }}. Su contenido ya no cambia.
                    </span>
                </span>
                <flux:button size="sm" variant="danger" :href="route('intranet.kits.pdf', $kit)" target="_blank" class="max-md:h-11 max-md:w-full">
                    <i class="fa-solid fa-file-pdf text-sm" aria-hidden="true"></i>
                    PDF
                </flux:button>
            </div>
        @endif

        <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-[minmax(0,1.55fr)_minmax(0,1fr)] lg:grid-rows-[auto_1fr]">
            <div class="flex flex-col gap-3 lg:col-start-2 lg:row-start-1">
                <div class="flex flex-col gap-2.5 rounded-card border border-line bg-white p-4">
                    <div class="flex flex-wrap items-baseline gap-2">
                        <span class="font-heading text-[2rem] leading-none font-medium {{ $isComplete ? 'text-badge-success-ink' : 'text-brand' }}">{{ $totalAdded }} de {{ $totalQuantity }}</span>
                        <span class="text-sm text-ink-muted">dispositivos{{ $isComplete ? '' : ' · faltan '.($totalQuantity - $totalAdded) }}</span>
                    </div>
                    @include('pages.kits.partials.progress', ['label' => 'Avance del kit', 'added' => $totalAdded, 'quantity' => $totalQuantity])
                </div>

                @if (! $canChange)
                    {{-- A dispatched kit tells it above, with its PDF. --}}
                @elseif ($isComplete)
                    <p role="status" class="m-0 flex gap-2.5 rounded-control bg-badge-success p-3.5 text-sm leading-normal text-badge-success-ink">
                        <x-feather name="check-circle" class="mt-0.5 size-4.5" />
                        <span><strong>Kit completo.</strong> {{ $kit->pouch_code }} quedó Armado y listo para despachar.</span>
                    </p>
                @else
                    <p role="status" class="m-0 flex gap-2.5 rounded-control bg-badge-warning p-3.5 text-sm leading-normal text-badge-warning-ink">
                        <x-feather name="alert-triangle" class="mt-0.5 size-4.5" />
                        <span>Faltan {{ $missingText }}. El kit se guarda así y sigue <strong>Armando</strong>: no se despacha hasta completarlo.</span>
                    </p>
                @endif
            </div>

            <div class="flex flex-col gap-3 lg:col-start-1 lg:row-span-2 lg:row-start-1">
                @foreach ($kitSlots as $kitSlot)
                    @include('pages.kits.partials.slot', ['devices' => $devicesBySlot->get($kitSlot['key'], collect()), 'canChange' => $canChange])
                @endforeach
            </div>

            <div wire:ignore @class(['overflow-hidden rounded-card border border-line bg-white lg:col-start-2 lg:row-start-2', 'hidden' => ! $canChange])>
                <h3 class="m-0 bg-table-head px-3.5 py-2.5 font-sans text-xs font-medium tracking-wider text-ink-muted uppercase">Últimos escaneos</h3>
                <p x-show="scans.length === 0" class="m-0 px-3.5 py-3 text-sm text-ink-muted">Aquí verás cada serie que escanees: en verde lo agregado y en rojo lo que no entró, con el motivo.</p>
                <ul role="log" aria-live="polite" class="m-0 list-none p-0">
                    <template x-for="scan in scans" :key="scan.id">
                        <li class="flex items-center gap-2.5 border-b border-line px-3.5 py-2.5 text-sm last:border-b-0">
                            @include('pages.kits.partials.scan-icon')
                            <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                                <strong class="font-semibold break-all" x-text="scan.serialNumber"></strong>
                                <small class="text-[0.8125rem]" :class="scan.state === 'added' || scan.state === 'pending' ? 'text-ink-muted' : 'text-badge-danger-ink'" x-text="scan.message"></small>
                            </span>
                            <flux:button size="sm" x-show="scan.state === 'failed'" x-on:click="retry(scan)" class="max-md:h-11">
                                <i class="fa-solid fa-rotate-right text-sm" aria-hidden="true"></i>
                                Reintentar
                            </flux:button>
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line pt-3.5">
            <span class="text-sm text-ink-muted">{{ $canChange ? 'Al quitar una serie, vuelve a quedar Disponible.' : 'Un kit despachado no cambia su contenido.' }}</span>
            <flux:button :href="route('intranet.kits.index')" wire:navigate class="max-md:h-11 max-md:w-full">Volver a Kits</flux:button>
        </div>
    </flux:card>
</div>
