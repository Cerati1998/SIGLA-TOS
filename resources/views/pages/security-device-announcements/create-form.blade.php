<div x-data="announcementLots({{ \App\Models\SecurityDeviceAnnouncement::MAX_DEVICES }}, {{ $form->savedDevices }}, {{ $savedLots->count() }}, @js($this->securityDeviceTypes->mapWithKeys(fn ($type) => [$type->id => ['name' => $type->name, 'usesSeries' => $type->uses_series, 'requiresApproval' => $type->requires_lot_approval]])))">
    <form wire:submit="save" class="flex max-w-250 flex-col gap-5.25">
    <x-panel.form-card class="max-w-none">
        <x-slot:heading>
            <span class="flex items-center gap-2.5"><span class="inline-flex size-6.5 flex-none items-center justify-center rounded-full bg-brand text-sm font-semibold text-white">1</span> Anuncio</span>
        </x-slot:heading>

        @if ($announcement === null)
            <fieldset wire:loading.attr="disabled" wire:target="save" class="col-span-full grid grid-cols-1 items-start gap-x-6 gap-y-3.5 md:grid-cols-4">
                <flux:select wire:model="form.shippingLineId" label="Línea naviera" placeholder="Elige una línea naviera" required>
                    @foreach ($this->shippingLines as $shippingLine)
                        <flux:select.option value="{{ $shippingLine->id }}">{{ $shippingLine->code }} · {{ $shippingLine->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="form.providerId" label="Proveedor" placeholder="Elige un proveedor" required>
                    @foreach ($this->providers as $provider)
                        <flux:select.option value="{{ $provider->id }}">{{ $provider->business_name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="form.receivedAt" label="Fecha de recepción" type="date" required />

                <flux:input wire:model="form.dispatchNumber" label="Número de guía" type="text" maxlength="50" required autocomplete="off" />
            </fieldset>

            <p class="col-span-full m-0 text-sm text-ink-muted">Todos los lotes del anuncio son de esta naviera y llegan con esta guía.</p>
        @else
            <dl class="col-span-full m-0 grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach ([
                    'Línea naviera' => $announcement->shippingLine->code.' · '.$announcement->shippingLine->name,
                    'Proveedor' => $announcement->provider->business_name,
                    'Recepción' => $announcement->received_at->format('d/m/Y'),
                    'Guía' => $announcement->dispatch_number,
                ] as $label => $value)
                    <div class="flex min-w-0 flex-col gap-0.5">
                        <dt class="text-xs tracking-wider text-ink-muted uppercase">{{ $label }}</dt>
                        <dd class="m-0 break-words">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </x-panel.form-card>

    <x-panel.form-card class="max-w-none">
        <x-slot:heading>
            <span class="flex items-center gap-2.5"><span class="inline-flex size-6.5 flex-none items-center justify-center rounded-full bg-brand text-sm font-semibold text-white">2</span> Agregar lote</span>
        </x-slot:heading>

        <div
            x-data="securityDeviceLotPreview({{ \App\Models\SecurityDeviceAnnouncement::MAX_DEVICES }}, @js($this->securityDeviceTypeBoxes))"
            x-on:announcement-lot-added.window="clearQuantity"
            x-on:keydown.enter="addOnEnter($event, preview, usesSeries)"
            class="col-span-full flex flex-col gap-3.5"
        >
            <div class="grid grid-cols-2 items-start gap-x-6 gap-y-3.5 md:grid-cols-6">
                <div class="col-span-2 md:col-span-3">
                    <flux:select x-model="draft.securityDeviceTypeId" label="Tipo de dispositivo" placeholder="Elige un tipo" description:trailing="Solo tipos activos.">
                        @foreach ($this->securityDeviceTypes as $securityDeviceType)
                            <flux:select.option value="{{ $securityDeviceType->id }}">{{ $securityDeviceType->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="max-md:hidden md:col-span-3" aria-hidden="true"></div>

                <div x-show="usesSeries" class="md:col-span-2">
                    <flux:input x-model="draft.seriesPrefix" label="Inicio de serie" type="text" maxlength="20" autocomplete="off" class:input="uppercase" />
                </div>
                <div class="md:col-span-2" x-bind:class="{ 'col-span-2': !usesSeries }">
                    <flux:input x-model="draft.minDigits" x-digits-only label="Mínimo de dígitos" type="text" inputmode="numeric" maxlength="2" autocomplete="off" />
                </div>
                <div x-show="!usesSeries" x-cloak class="max-md:hidden md:col-span-4" aria-hidden="true"></div>
                <div x-show="usesSeries" class="col-span-2">
                    <flux:input x-model="draft.seriesSuffix" label="Final de serie (opcional)" description:trailing="Letras al final, como la A de GWF00100A." type="text" maxlength="10" autocomplete="off" class:input="uppercase" />
                </div>
                <div class="md:col-span-2">
                    <flux:input x-model="draft.seriesStart" x-on:input="startChanged" x-digits-only label="Correlativo inicial" type="text" inputmode="numeric" maxlength="15" autocomplete="off" />
                </div>
                <div class="md:col-span-2">
                    <flux:input x-model="quantity" x-on:input="quantityChanged" x-digits-only label="Cantidad" description:trailing="Calcula el correlativo final." type="text" inputmode="numeric" maxlength="5" autocomplete="off" />
                </div>
                <div class="col-span-2">
                    <flux:input x-model="draft.seriesEnd" x-on:input="endChanged" x-digits-only label="Correlativo final" type="text" inputmode="numeric" maxlength="15" autocomplete="off" />
                </div>
                <p x-show="!usesSeries" x-cloak class="col-span-full m-0 text-sm text-ink-muted">
                    Este tipo no utiliza serie: no se piden el inicio ni el final de serie, y cada dispositivo es su correlativo con ceros a la izquierda hasta el mínimo de dígitos.
                </p>
            </div>

            <p x-show="preview.error === null && preview.quantity === null" class="m-0 text-sm text-ink-muted">
                Completa la serie y el rango para ver la vista previa.
            </p>

            <p x-show="preview.error !== null" x-cloak x-text="preview.error" role="alert" class="m-0 text-sm text-danger"></p>

            <p x-show="preview.quantity !== null" x-cloak role="status" class="m-0 flex gap-2.5 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
                <x-feather name="info" class="mt-0.5 size-4.5 flex-none" />
                <span>
                    <strong x-text="preview.quantity + (preview.quantity === '1' ? ' dispositivo' : ' dispositivos')"></strong>
                    · primera serie <strong x-text="preview.first"></strong>
                    · última serie <strong x-text="preview.last"></strong>
                </span>
            </p>

            <p x-show="!requiresApproval" x-cloak role="status" class="m-0 flex gap-2.5 rounded-control bg-badge-success p-3.5 text-sm text-badge-success-ink">
                <x-feather name="check" class="mt-0.5 size-4.5 flex-none" />
                <span>Este tipo no requiere aprobación en lote: sus dispositivos quedarán <strong>Disponibles</strong> al guardar.</span>
            </p>

            <flux:textarea x-model="draft.notes" label="Observaciones del lote (opcional)" rows="2" />

            <p x-show="draftError !== null" x-cloak x-text="draftError" role="alert" class="m-0 text-sm text-danger"></p>

            <div class="flex justify-end">
                <flux:button type="button" x-on:click="addDraft(preview, usesSeries)" class="max-md:h-11 max-md:w-full">
                    <i class="fa-solid fa-plus text-sm" aria-hidden="true"></i>
                    Agregar al anuncio
                </flux:button>
            </div>
        </div>
    </x-panel.form-card>

    <x-panel.list-card>
        <div class="flex flex-wrap items-center gap-2.5 border-b border-line p-5.25">
            <span class="inline-flex size-6.5 flex-none items-center justify-center rounded-full bg-brand text-sm font-semibold text-white">3</span>
            <h3 class="m-0 flex-[1_1_auto] font-heading text-h3 font-normal">Lotes del anuncio</h3>
            <span class="text-sm text-ink-muted"><strong class="text-ink" x-text="summary"></strong> de {{ number_format(\App\Models\SecurityDeviceAnnouncement::MAX_DEVICES, 0, ',', ' ') }} como máximo</span>
        </div>

        @error('form.lots')
            <div role="alert" class="m-5.25 mb-0 flex gap-2.5 rounded-control bg-badge-danger p-3.5 text-sm text-badge-danger-ink">
                <x-feather name="alert-circle" class="mt-0.5 size-4.5 flex-none" />
                <span class="flex flex-col gap-1">
                    <strong>{{ $announcement === null ? 'No se guardó el anuncio.' : 'No se guardaron los lotes.' }}</strong>
                    @foreach ($errors->get('form.lots') as $lotError)
                        <span>{{ $lotError }}</span>
                    @endforeach
                </span>
            </div>
        @enderror

        <p x-show="lots.length === 0 && savedLotsCount === 0" class="m-5.25 rounded-card border border-dashed border-ink-muted p-6 text-center text-sm text-ink-muted">
            Aún no agregas lotes.<br>Agrega al menos uno para guardar el anuncio.
        </p>

        <div x-show="lots.length > 0 || savedLotsCount > 0" class="overflow-x-auto">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-table-head text-left text-xs font-medium tracking-wider text-ink-muted uppercase">
                        <th class="border-b border-line px-5 py-3 font-medium">N.º</th>
                        <th class="border-b border-line px-5 py-3 font-medium">Tipo</th>
                        <th class="border-b border-line px-5 py-3 font-medium">Primera serie</th>
                        <th class="border-b border-line px-5 py-3 font-medium">Última serie</th>
                        <th class="border-b border-line px-5 py-3 text-right font-medium">Cantidad</th>
                        <th class="border-b border-line px-5 py-3 font-medium max-md:hidden">Observaciones</th>
                        <th class="border-b border-line px-5 py-3 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($savedLots as $savedLot)
                        <tr wire:key="saved-lot-{{ $savedLot->id }}" class="border-b border-line bg-surface text-ink-muted">
                            <td class="px-5 py-3.5">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3.5">{{ $savedLot->securityDeviceType->name }}</td>
                            <td class="px-5 py-3.5 whitespace-nowrap">{{ $savedLot->serialNumber($savedLot->series_start) }}</td>
                            <td class="px-5 py-3.5 whitespace-nowrap">{{ $savedLot->serialNumber($savedLot->series_end) }}</td>
                            <td class="px-5 py-3.5 text-right">{{ number_format($savedLot->quantity, 0, ',', ' ') }}</td>
                            <td class="px-5 py-3.5 max-md:hidden">{{ $savedLot->notes ?? '—' }}</td>
                            <td class="px-5 py-3.5">Guardado</td>
                        </tr>
                    @endforeach

                    <template x-for="(lot, index) in lots" x-bind:key="index">
                        <tr class="border-b border-line even:bg-surface" x-bind:class="{ 'bg-badge-danger!': isColliding(index) }">
                            <td class="px-5 py-3.5" x-text="savedLotsCount + index + 1"></td>
                            <td class="px-5 py-3.5" x-text="typeName(lot)"></td>
                            <td class="px-5 py-3.5 whitespace-nowrap" x-text="serialNumber(lot, lot.seriesStart)"></td>
                            <td class="px-5 py-3.5 whitespace-nowrap" x-text="serialNumber(lot, lot.seriesEnd)"></td>
                            <td class="px-5 py-3.5 text-right" x-text="quantityLabel(lot)"></td>
                            <td class="px-5 py-3.5 max-md:hidden" x-text="lot.notes || '—'"></td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1 [&_button]:max-md:size-11">
                                    <x-wire-button primary x-on:click="view(index)" title="Ver lote" aria-label="Ver lote" xs>
                                        <i class="fa-solid fa-eye text-sm" aria-hidden="true"></i>
                                    </x-wire-button>
                                    <x-wire-button negative x-on:click="remove(index)" title="Quitar lote" aria-label="Quitar lote" xs>
                                        <i class="fa-solid fa-trash text-sm" aria-hidden="true"></i>
                                    </x-wire-button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <p class="m-0 px-5.25 py-3 text-sm text-ink-muted">Un anuncio puede traer lotes de tipos distintos. Quitar un lote de esta lista no pide confirmación: todavía no se guardó nada.</p>
    </x-panel.list-card>

    <div class="flex flex-wrap justify-end gap-3">
        <flux:button :href="$cancelUrl" wire:navigate class="max-md:h-11 max-md:w-full">Cancelar</flux:button>
        <flux:button variant="primary" type="submit" :loading="false" x-bind:disabled="lots.length === 0" class="max-md:h-11 max-md:w-full">
            <span wire:loading.remove wire:target="save">{{ $announcement === null ? 'Guardar anuncio' : 'Guardar lotes nuevos' }}</span>
            <span wire:loading.flex wire:target="save" class="items-center gap-2">
                <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                Guardando…
            </span>
        </flux:button>
    </div>
    </form>

    <x-wire-modal-card title="Lote del anuncio" name="announcement-lot-view">
        <div class="grid gap-4" x-show="viewed !== null">
            <h4 class="m-0 font-heading text-h3 font-normal text-brand" x-text="viewedTitle"></h4>
            <dl class="m-0 grid grid-cols-2 gap-3">
                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs tracking-wider text-ink-muted uppercase">Rango</dt>
                    <dd class="m-0" x-text="viewedRange"></dd>
                </div>
                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs tracking-wider text-ink-muted uppercase">Cantidad</dt>
                    <dd class="m-0" x-text="viewedQuantity"></dd>
                </div>
                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs tracking-wider text-ink-muted uppercase">Estado al guardar</dt>
                    <dd class="m-0" x-text="viewedStatus"></dd>
                </div>
                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs tracking-wider text-ink-muted uppercase">Observaciones</dt>
                    <dd class="m-0 break-words" x-text="viewedNotes"></dd>
                </div>
            </dl>

            <flux:input x-model="viewedSearch" x-on:input="viewedPage = 0" label="Buscar serie" type="search" autocomplete="off" />

            <ul class="m-0 list-none rounded-card border border-line p-0 text-sm">
                <template x-for="serialNumber in viewedPageSerialNumbers" x-bind:key="serialNumber">
                    <li class="border-b border-line px-3.5 py-2.5 last:border-b-0 even:bg-surface" x-text="serialNumber"></li>
                </template>
            </ul>

            <div class="flex items-center justify-between gap-3 text-sm text-ink-muted">
                <span x-text="viewedPageLabel"></span>
                <span class="flex gap-2">
                    <flux:button type="button" size="sm" x-on:click="previousViewedPage" x-bind:disabled="viewedPage === 0">Anterior</flux:button>
                    <flux:button type="button" size="sm" x-on:click="nextViewedPage" x-bind:disabled="!hasNextViewedPage">Siguiente</flux:button>
                </span>
            </div>
        </div>

        <x-slot:footer>
            <div class="flex justify-end">
                <flux:button type="button" x-on:click="close">Cerrar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>

@script
<script>
    /**
     * The lots of an announcement of the entry of lots (H37), listed in the browser: the draft lot is checked and added
     * to the list, which can be viewed and emptied without asking the server. The list goes to the server only on save,
     * in form.lots, where every rule is checked again.
     */
    Alpine.data('announcementLots', (maxDevices, savedDevices, savedLotsCount, types) => {
        const formatNumber = (number) => String(number).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        const emptyDraft = () => ({ securityDeviceTypeId: '', seriesPrefix: '', minDigits: '', seriesStart: '', seriesEnd: '', seriesSuffix: '', notes: '' });
        const pageSize = 10;

        return {
            draft: emptyDraft(),
            draftError: null,
            lots: [...($wire.form.lots ?? [])],
            savedLotsCount,
            viewedIndex: 0,
            viewed: null,
            viewedSearch: '',
            viewedPage: 0,

            isColliding(index) {
                return ($wire.form.collidingLots ?? []).includes(index);
            },

            typeName(lot) {
                return types[lot.securityDeviceTypeId]?.name ?? '—';
            },

            usesSeries(lot) {
                return types[lot.securityDeviceTypeId]?.usesSeries ?? true;
            },

            serialNumber(lot, correlative) {
                const prefix = this.usesSeries(lot) ? lot.seriesPrefix : '';
                const suffix = this.usesSeries(lot) ? lot.seriesSuffix : '';

                return prefix + String(correlative).padStart(Number(lot.minDigits), '0') + suffix;
            },

            count(lot) {
                return Number(lot.seriesEnd) - Number(lot.seriesStart) + 1;
            },

            quantityLabel(lot) {
                return formatNumber(this.count(lot));
            },

            get totalDevices() {
                return savedDevices + this.lots.reduce((total, lot) => total + this.count(lot), 0);
            },

            get summary() {
                const lotsCount = this.savedLotsCount + this.lots.length;

                return `${lotsCount === 1 ? '1 lote' : lotsCount + ' lotes'} · ${formatNumber(this.totalDevices)} series`;
            },

            /**
             * The serial numbers of a lot, kept by type scope: two types that do not use a series may share one (T62).
             */
            collides(lot, other) {
                if (this.usesSeries(lot) === false && this.usesSeries(other) === false && lot.securityDeviceTypeId !== other.securityDeviceTypeId) {
                    return null;
                }

                const start = Math.max(Number(lot.seriesStart), Number(other.seriesStart));
                const end = Math.min(Number(lot.seriesEnd), Number(other.seriesEnd));

                for (let correlative = start; correlative <= end; correlative++) {
                    const serialNumber = this.serialNumber(lot, correlative);

                    if (serialNumber === this.serialNumber(other, correlative)) {
                        return serialNumber;
                    }
                }

                return null;
            },

            /**
             * Add the draft lot to the list when its preview is right, it does not pass the maximum of the announcement and
             * none of its serial numbers is in another listed lot (H37, criteria 3, 5 and 6).
             */
            addDraft(preview, usesSeries) {
                const lot = {
                    ...this.draft,
                    seriesPrefix: usesSeries ? this.draft.seriesPrefix.trim().toUpperCase() : '',
                    seriesSuffix: usesSeries ? this.draft.seriesSuffix.trim().toUpperCase() : '',
                    notes: this.draft.notes.trim(),
                };

                if (lot.securityDeviceTypeId === '') {
                    this.draftError = 'Elige el tipo de dispositivo del lote.';

                    return;
                }

                if (preview.error !== null || preview.quantity === null) {
                    this.draftError = preview.error ?? 'Completa la serie y el rango del lote.';

                    return;
                }

                if (this.totalDevices + this.count(lot) > maxDevices) {
                    this.draftError = `Un anuncio trae como máximo ${formatNumber(maxDevices)} series. Ya lleva ${formatNumber(this.totalDevices)} y este lote trae ${formatNumber(this.count(lot))}.`;

                    return;
                }

                for (const [index, other] of this.lots.entries()) {
                    const serialNumber = this.collides(lot, other);

                    if (serialNumber !== null) {
                        this.draftError = `Este rango choca con el lote ${this.savedLotsCount + index + 1} del anuncio: los dos traen la serie ${serialNumber}.`;

                        return;
                    }
                }

                this.lots = [...this.lots, lot];
                this.sync();
                this.draft = { ...emptyDraft(), securityDeviceTypeId: lot.securityDeviceTypeId };
                this.draftError = null;
                this.$dispatch('announcement-lot-added');
            },

            /**
             * Add the draft lot with Enter, instead of saving the whole announcement. Enter still breaks lines in the notes.
             */
            addOnEnter(event, preview, usesSeries) {
                if (event.target.tagName === 'TEXTAREA') {
                    return;
                }

                event.preventDefault();
                this.addDraft(preview, usesSeries);
            },

            remove(index) {
                this.lots = this.lots.filter((lot, position) => position !== index);
                this.sync();
            },

            /**
             * Leave the list in the Livewire form, to be sent on save without asking the server now.
             */
            sync() {
                $wire.$set('form.lots', this.lots, false);
            },

            view(index) {
                this.viewed = this.lots[index];
                this.viewedIndex = index;
                this.viewedSearch = '';
                this.viewedPage = 0;
                this.$openModal('announcement-lot-view');
            },

            get viewedTitle() {
                return this.viewed === null ? '' : `Lote ${this.savedLotsCount + this.viewedIndex + 1} · ${this.typeName(this.viewed)}`;
            },

            get viewedRange() {
                return this.viewed === null ? '' : `${this.serialNumber(this.viewed, this.viewed.seriesStart)} – ${this.serialNumber(this.viewed, this.viewed.seriesEnd)}`;
            },

            get viewedQuantity() {
                return this.viewed === null ? '' : this.quantityLabel(this.viewed);
            },

            get viewedStatus() {
                return this.viewed !== null && types[this.viewed.securityDeviceTypeId]?.requiresApproval === false ? 'Disponible' : 'Inactivo';
            },

            get viewedNotes() {
                return this.viewed?.notes || 'Sin observaciones.';
            },

            /**
             * The serial numbers of the viewed lot that match the search, built only when the modal is open.
             */
            get viewedSerialNumbers() {
                if (this.viewed === null) {
                    return [];
                }

                const search = this.viewedSearch.trim().toUpperCase();
                const serialNumbers = [];

                for (let correlative = Number(this.viewed.seriesStart); correlative <= Number(this.viewed.seriesEnd); correlative++) {
                    const serialNumber = this.serialNumber(this.viewed, correlative);

                    if (search === '' || serialNumber.includes(search)) {
                        serialNumbers.push(serialNumber);
                    }
                }

                return serialNumbers;
            },

            get viewedPageSerialNumbers() {
                return this.viewedSerialNumbers.slice(this.viewedPage * pageSize, (this.viewedPage + 1) * pageSize);
            },

            get hasNextViewedPage() {
                return (this.viewedPage + 1) * pageSize < this.viewedSerialNumbers.length;
            },

            get viewedPageLabel() {
                const total = this.viewedSerialNumbers.length;

                if (total === 0) {
                    return 'Ninguna serie coincide.';
                }

                return `Mostrando ${this.viewedPage * pageSize + 1} a ${Math.min((this.viewedPage + 1) * pageSize, total)} de ${formatNumber(total)} series.`;
            },

            previousViewedPage() {
                this.viewedPage = Math.max(0, this.viewedPage - 1);
            },

            nextViewedPage() {
                if (this.hasNextViewedPage) {
                    this.viewedPage++;
                }
            },
        };
    });
</script>
@endscript
