@php
    use App\Services\Sigla1LookupStatus;

    $hasRequestToCertify = $gateOutRequest !== null && $existingCertification === null;
    $value = fn (?string ...$parts): string => implode(' · ', array_filter($parts, fn (?string $part): bool => $part !== null)) ?: '—';
@endphp

<div x-data="certificationStart" class="flex flex-col gap-5.25 max-md:pb-28">
    <div class="flex flex-col gap-2.5 md:hidden">
        <span class="text-sm text-ink-muted" x-text="stepLabel"></span>
        <div class="grid grid-cols-3 gap-1.5" aria-hidden="true">
            @foreach ([1, 2, 3] as $number)
                <i wire:key="step-bar-{{ $number }}" class="block h-1.5 rounded-full" x-bind:class="barClass({{ $number }})"></i>
            @endforeach
        </div>
    </div>

    {{-- Step 1: the gate out request --}}
    <flux:card class="flex flex-col gap-4 p-5.25! max-md:p-4!" x-bind:class="stepClass(1)">
        <x-panel.form-step number="1" title="Solicitud de retiro" text="Busca la solicitud con el contenedor y el booking. Sus datos llegan de SIGLA 1.0 y no se editan." />

        <form wire:submit="search" novalidate class="grid grid-cols-1 items-start gap-x-4 gap-y-3.5 md:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)_auto]">
            <fieldset class="m-0 flex min-w-0 flex-col gap-1.5 border-0 p-0">
                <legend class="mb-1.5 p-0 text-sm font-medium">Contenedor</legend>
                <div class="grid grid-cols-[5fr_7fr] gap-2">
                    <div class="flex min-w-0 flex-col gap-1">
                        <input
                            x-ref="containerOwner"
                            wire:model="form.containerOwner"
                            x-on:input="lettersOnly"
                            type="text"
                            maxlength="4"
                            autocomplete="off"
                            spellcheck="false"
                            aria-label="Contenedor, 4 letras"
                            @class(['h-11 w-full rounded-control border bg-white px-3 text-lg tracking-wider uppercase max-md:h-13', 'border-danger' => $errors->has('form.containerOwner'), 'border-control' => ! $errors->has('form.containerOwner')])
                        />
                        <small class="text-sm text-ink-muted">4 letras</small>
                    </div>
                    <div class="flex min-w-0 flex-col gap-1">
                        <input
                            x-ref="containerNumber"
                            wire:model="form.containerNumber"
                            x-digits-only
                            type="text"
                            inputmode="numeric"
                            maxlength="7"
                            autocomplete="off"
                            aria-label="Contenedor, 7 números"
                            @class(['h-11 w-full rounded-control border bg-white px-3 text-lg tracking-wider max-md:h-13', 'border-danger' => $errors->has('form.containerNumber'), 'border-control' => ! $errors->has('form.containerNumber')])
                        />
                        <small class="text-sm text-ink-muted">7 números</small>
                    </div>
                </div>
                <flux:error name="form.containerOwner" />
                <flux:error name="form.containerNumber" />
            </fieldset>

            <div class="flex min-w-0 flex-col gap-1.5">
                <label for="booking" class="text-sm font-medium">Booking</label>
                <input
                    id="booking"
                    wire:model="form.booking"
                    x-on:input="upperCase"
                    type="text"
                    maxlength="50"
                    autocomplete="off"
                    spellcheck="false"
                    @class(['h-11 w-full rounded-control border bg-white px-3 text-lg uppercase max-md:h-13', 'border-danger' => $errors->has('form.booking'), 'border-control' => ! $errors->has('form.booking')])
                />
                <flux:error name="form.booking" />
            </div>

            <flux:button type="submit" variant="primary" class="h-11! md:mt-6.5 max-md:h-13! max-md:w-full">
                <i class="fa-solid fa-magnifying-glass text-sm" aria-hidden="true"></i>
                <span wire:loading.remove wire:target="search">Buscar solicitud</span>
                <span wire:loading wire:target="search">Buscando…</span>
            </flux:button>
        </form>

        <p wire:loading.flex wire:target="search" role="status" class="m-0 items-center gap-2 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
            <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
            Buscando la solicitud en SIGLA 1.0…
        </p>

        <div wire:loading.remove wire:target="search" class="flex flex-col gap-4">
            @switch($status)
                @case(Sigla1LookupStatus::Found)
                    @if ($existingCertification !== null)
                        <div role="status" class="flex flex-col items-start gap-2.5 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
                            <span>
                                La solicitud {{ $gateOutRequest['reference'] }} (contenedor <strong>{{ $existingCertification->container }}</strong>) ya tiene una certificación,
                                iniciada el {{ $existingCertification->started_at->format('d/m/Y') }} a las {{ $existingCertification->started_at->format('H:i') }}
                                por <strong>{{ $existingCertification->certifier->name }}</strong>.
                            </span>
                            <flux:button variant="primary" :href="route('intranet.certifications.show', $existingCertification)" wire:navigate class="max-md:h-13 max-md:w-full">Abrir certificación</flux:button>
                        </div>
                    @else
                        @include('pages.certifications.partials.request-summary', ['gateOutRequest' => $gateOutRequest, 'matchedShippingLine' => $this->matchedShippingLine, 'value' => $value])

                        @if ($this->matchedShippingLine === null)
                            @include('pages.certifications.partials.shipping-line-choice', ['sigla1Name' => $gateOutRequest['shipping_line'], 'candidateIds' => $shippingLineCandidateIds, 'options' => $this->shippingLineOptions])
                        @endif
                    @endif
                    @break

                @case(Sigla1LookupStatus::NotFound)
                    <p role="status" class="m-0 flex items-start gap-2 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
                        <i class="fa-solid fa-triangle-exclamation mt-0.5" aria-hidden="true"></i>
                        <span>No encontramos una solicitud de retiro con el contenedor <strong>{{ $form->container() }}</strong> y el booking <strong>{{ $form->booking }}</strong>. Revisa los dos datos.</span>
                    </p>
                    @break

                @case(Sigla1LookupStatus::Unavailable)
                    <div role="status" class="flex flex-col items-start gap-2.5 rounded-control bg-badge-danger p-3.5 text-sm text-badge-danger-ink">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                            SIGLA 1.0 no responde en este momento. Intenta de nuevo en unos segundos.
                        </span>
                        <flux:button wire:click="search" wire:loading.attr="disabled" wire:target="search" class="max-md:h-13 max-md:w-full">
                            <i class="fa-solid fa-rotate-right text-sm" aria-hidden="true"></i>
                            Reintentar
                        </flux:button>
                    </div>
                    @break
            @endswitch
        </div>
    </flux:card>

    {{-- Step 2: the kit --}}
    @if ($hasRequestToCertify)
        <flux:card class="flex flex-col gap-4 p-5.25! max-md:p-4!" x-bind:class="stepClass(2)" wire:key="step-kit">
            <x-panel.form-step number="2" title="Kit" text="Escanea o escribe el código de la funda y pulsa Enter. Sirve un kit Despachado de la misma naviera de la solicitud." />

            <fieldset x-bind:disabled="! shippingLineChosen" class="m-0 flex flex-col gap-3.5 border-0 p-0 md:hidden">
                <x-camera-scanner title="Escanear la funda" label="Escanear la funda con la cámara" x-on:barcode-detected="scannedPouch" />
                <span class="flex items-center gap-2.5 text-sm text-ink-muted before:h-px before:flex-1 before:bg-line after:h-px after:flex-1 after:bg-line">o escribe el código</span>
            </fieldset>

            <fieldset x-bind:disabled="! shippingLineChosen" x-bind:class="! shippingLineChosen && 'opacity-60'" class="m-0 grid grid-cols-1 items-start gap-4 border-0 p-0 md:grid-cols-2">
                <div class="flex min-w-0 flex-col gap-1.5">
                    <flux:label for="pouch-code">Código de barras de la funda</flux:label>
                    <div class="flex gap-2">
                        <div class="min-w-0 flex-1">
                            <x-scan-input
                                id="pouch-code"
                                x-ref="pouchCode"
                                wire:model="form.pouchCode"
                                :invalid="$errors->has('form.pouchCode')"
                                x-bind:placeholder="shippingLineChosen ? 'Escanea o escribe' : 'Elige primero la naviera'"
                                enterkeyhint="search"
                                x-on:keydown.enter.prevent="findKit"
                            />
                        </div>
                        <flux:button variant="primary" wire:click="findKit" wire:loading.attr="disabled" wire:target="findKit" class="h-11! w-12 max-md:h-13! max-md:w-14" aria-label="Buscar la funda" title="Buscar la funda">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        </flux:button>
                    </div>
                    <flux:error name="form.pouchCode" />
                </div>

                <div class="flex min-w-0 flex-col gap-1.5">
                    <flux:label for="inspection-sheet-code">Código de barras del formato <span class="font-normal text-ink-muted">(opcional)</span></flux:label>
                    <div class="flex gap-2">
                        <div class="min-w-0 flex-1">
                            <x-scan-input
                                id="inspection-sheet-code"
                                x-ref="inspectionSheetCode"
                                wire:model="form.inspectionSheetCode"
                                :invalid="$errors->has('form.inspectionSheetCode')"
                                placeholder="Escanea o escribe"
                                x-on:keydown.enter.prevent
                            />
                        </div>
                        <x-camera-scanner title="Escanear el formato" class="md:hidden" x-on:barcode-detected="scannedSheet" />
                    </div>
                    <span class="text-sm text-ink-muted">Si el kit trae formato de inspección, escanéalo aquí. No se repite en otra certificación.</span>
                    <flux:error name="form.inspectionSheetCode" />
                </div>
            </fieldset>

            <p wire:loading.flex wire:target="findKit" role="status" class="m-0 items-center gap-2 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
                <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                Buscando el kit…
            </p>

            @if ($this->kit !== null)
                @include('pages.certifications.partials.kit-summary', ['kit' => $this->kit, 'contents' => $this->kitContents])
            @endif
        </flux:card>
    @endif

    {{-- Step 3: the yard zone and the start --}}
    @if ($hasRequestToCertify && $this->kit !== null)
        <flux:card class="flex flex-col gap-4 p-5.25! max-md:p-4!" x-bind:class="stepClass(3)" wire:key="step-zone">
            <x-panel.form-step number="3" title="Zona de patio" text="Dónde se certifica la unidad. Solo zonas activas de tu sede." />

            <fieldset class="m-0 grid grid-cols-1 gap-2.5 border-0 p-0 md:grid-cols-3" aria-describedby="yard-zone-error">
                <legend class="sr-only">Zona de patio</legend>
                @forelse ($this->yardZones as $yardZone)
                    <label wire:key="yard-zone-{{ $yardZone->id }}" @class([
                        'flex min-h-14 cursor-pointer items-center gap-3 rounded-lg border bg-white px-3.5 text-base has-checked:border-2 has-checked:border-brand has-checked:bg-badge-info has-checked:font-medium has-checked:text-brand',
                        'border-danger' => $errors->has('form.yardZoneId'),
                        'border-control' => ! $errors->has('form.yardZoneId'),
                    ])>
                        <input type="radio" wire:model="form.yardZoneId" value="{{ $yardZone->id }}" class="size-5.5 accent-brand">
                        {{ $yardZone->name }}
                    </label>
                @empty
                    <p class="col-span-full m-0 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">Tu sede no tiene zonas de patio activas. Pide que se cree una en Zonas de patio.</p>
                @endforelse
            </fieldset>
            <div id="yard-zone-error"><flux:error name="form.yardZoneId" /></div>

            <dl class="m-0 flex flex-col rounded-card border border-line px-4 py-1 md:hidden">
                <div class="flex min-h-14 items-center gap-3 py-2">
                    <x-feather name="check" class="size-5 shrink-0 text-status-green" />
                    <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                        <dt class="text-xs tracking-wider text-ink-muted uppercase">Contenedor · Solicitud {{ $gateOutRequest['reference'] }}</dt>
                        <dd class="m-0 font-medium break-words">{{ $gateOutRequest['container'] ?? $form->container() }}</dd>
                    </div>
                    <button type="button" x-on:click="goTo(1)" class="h-11 px-1 text-base font-medium text-brand">Cambiar</button>
                </div>
                <div class="flex min-h-14 items-center gap-3 border-t border-surface py-2">
                    <x-feather name="check" class="size-5 shrink-0 text-status-green" />
                    <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                        <dt class="text-xs tracking-wider text-ink-muted uppercase">Kit · {{ count($this->kitContents['devices'] ?? []) }} dispositivos</dt>
                        <dd class="m-0 font-medium">{{ $this->kit->pouch_code }}</dd>
                    </div>
                    <button type="button" x-on:click="goTo(2)" class="h-11 px-1 text-base font-medium text-brand">Cambiar</button>
                </div>
            </dl>

            <p class="m-0 flex items-start gap-2 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
                <x-feather name="info" class="mt-0.5 size-4 shrink-0" />
                <span>Al iniciar se guardan quién certifica (<strong>{{ auth()->user()->name }}</strong>) y la fecha y hora de inicio, junto con la solicitud, el kit y la zona. Se guarda todo o nada.</span>
            </p>

            @if ($startFailed)
                <p role="alert" class="m-0 flex items-start gap-2 rounded-control bg-badge-danger p-3.5 text-sm text-badge-danger-ink">
                    <i class="fa-solid fa-circle-xmark mt-0.5" aria-hidden="true"></i>
                    No se pudo iniciar la certificación y no se guardó nada. Intenta de nuevo.
                </p>
            @endif

            <div class="flex flex-wrap justify-end gap-3 border-t border-line pt-4 max-md:hidden">
                <flux:button :href="route('intranet.certifications.create')" wire:navigate>Limpiar</flux:button>
                <flux:button variant="primary" wire:click="start" wire:loading.attr="disabled" wire:target="start">
                    <i class="fa-solid fa-play text-sm" aria-hidden="true"></i>
                    <span wire:loading.remove wire:target="start">Iniciar certificación</span>
                    <span wire:loading wire:target="start">Iniciando…</span>
                </flux:button>
            </div>
        </flux:card>
    @endif

    {{-- The bar of the phone, fixed at the bottom --}}
    <div class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-[1fr_2fr] gap-2.5 border-t border-line bg-white px-4 pt-3 pb-4 shadow-menu md:hidden">
        <template x-if="step === 1">
            <flux:button :href="route('intranet.certifications.create')" wire:navigate class="h-13!">Limpiar</flux:button>
        </template>
        <template x-if="step > 1">
            <flux:button x-on:click="back" class="h-13!">
                <i class="fa-solid fa-chevron-left text-sm" aria-hidden="true"></i>
                Atrás
            </flux:button>
        </template>

        <template x-if="step < 3">
            <flux:button variant="primary" x-on:click="next" x-bind:disabled="! canGoNext" class="h-13!">
                Siguiente
                <i class="fa-solid fa-chevron-right text-sm" aria-hidden="true"></i>
            </flux:button>
        </template>
        <template x-if="step === 3">
            <flux:button variant="primary" wire:click="start" wire:loading.attr="disabled" wire:target="start" class="h-13!">
                <i class="fa-solid fa-play text-sm" aria-hidden="true"></i>
                <span wire:loading.remove wire:target="start">Iniciar certificación</span>
                <span wire:loading wire:target="start">Iniciando…</span>
            </flux:button>
        </template>
    </div>
</div>

@script
<script>
    Alpine.data('certificationStart', () => ({
        step: 1,

        init() {
            this.$wire.$on('certification-step', ({ step }) => this.step = step);
        },

        get stepLabel() {
            return `Iniciar certificación · Paso ${this.step} de 3`;
        },

        get shippingLineChosen() {
            return this.$wire.matchedShippingLineId !== null || this.$wire.form.shippingLineId !== '';
        },

        get canGoNext() {
            if (this.step === 1) {
                return this.$wire.gateOutRequest !== null && this.$wire.existingCertificationId === null && this.shippingLineChosen;
            }

            return this.$wire.kitId !== null;
        },

        barClass(number) {
            if (number < this.step) {
                return 'bg-status-green';
            }

            return number === this.step ? 'bg-accent' : 'bg-line';
        },

        stepClass(number) {
            return number === this.step ? '' : 'max-md:hidden';
        },

        next() {
            if (this.canGoNext) {
                this.step++;
                this.$nextTick(() => window.scrollTo({ top: 0 }));
            }
        },

        back() {
            this.step = Math.max(1, this.step - 1);
            this.$nextTick(() => window.scrollTo({ top: 0 }));
        },

        goTo(number) {
            this.step = number;
            this.$nextTick(() => window.scrollTo({ top: 0 }));
        },

        lettersOnly(event) {
            const input = event.target;

            input.value = input.value.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 4);

            if (input.value.length === 4) {
                this.$refs.containerNumber.focus();
            }
        },

        upperCase(event) {
            const input = event.target;
            const caret = input.selectionStart;

            input.value = input.value.toUpperCase();
            input.setSelectionRange(caret, caret);
        },

        findKit() {
            this.$wire.findKit();
        },

        scannedPouch(event) {
            this.$wire.form.pouchCode = event.detail.value;
            this.$wire.findKit();
        },

        scannedSheet(event) {
            this.$wire.form.inspectionSheetCode = event.detail.value;
        },
    }));
</script>
@endscript
