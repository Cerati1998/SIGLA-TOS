<div x-data="bulkDispatch" x-on:confirm-bulk-dispatch.window="askToDispatch" x-on:client-created.window="chooseCreatedClient">
    <x-wire-modal-card
        title="Despacho a granel"
        name="bulk-dispatch-create"
        width="sm:max-w-5xl"
        wire:model="form.openModal"
        x-on:open="startDispatch"
        x-on:close="forgetDispatch"
    >
        <div class="flex flex-col gap-4.5">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="grid gap-1.75">
                    <x-wire-select
                        id="bulk-dispatch-receiver"
                        wire:model="form.receiverId"
                        label="Trabajador que recibe"
                        placeholder="Busca por nombre o documento"
                        :async-data="['api' => route('intranet.workers.options'), 'method' => 'POST']"
                        option-label="name"
                        option-value="id"
                        option-description="description"
                    />
                    <span class="text-sm text-ink-muted">Solo trabajadores activos.</span>
                </div>
                <div class="grid gap-1.75">
                    <x-wire-input id="bulk-dispatch-deliverer" label="Quién entrega" :value="$deliverer" disabled />
                    <span class="text-sm text-ink-muted">Eres tú, el usuario que despacha. Queda registrado solo.</span>
                </div>

                <fieldset class="m-0 flex flex-col gap-1.75 border-0 p-0">
                    <legend class="mb-1.75 p-0 text-sm font-medium text-ink">Motivo de salida</legend>
                    <div class="grid grid-cols-2 gap-2.5">
                        @foreach ($reasons as $reason)
                            <label for="bulk-dispatch-reason-{{ $reason->value }}" class="flex min-h-11 cursor-pointer items-center gap-2 rounded-control border border-control px-3 py-2 has-checked:border-2 has-checked:border-brand has-checked:bg-badge-info md:min-h-10.25">
                                <input id="bulk-dispatch-reason-{{ $reason->value }}" type="radio" wire:model="form.reason" value="{{ $reason->value }}" class="size-4 accent-brand">
                                <span>{{ $reason->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                    <flux:error name="form.reason" />
                </fieldset>

                <div>
                    <div x-show="isSale" x-cloak class="grid gap-1.75">
                        <x-wire-select
                            id="bulk-dispatch-client"
                            wire:model="form.clientId"
                            label="Cliente"
                            placeholder="Busca por razón social o documento"
                            :async-data="['api' => route('intranet.clients.options'), 'method' => 'POST']"
                            option-label="name"
                            option-value="id"
                            option-description="description"
                        >
                            <x-slot:corner>
                                <x-label-link x-on:click="$openModal('client-create')">+ Agregar</x-label-link>
                            </x-slot:corner>
                        </x-wire-select>
                        <span class="text-sm text-ink-muted">Solo clientes activos.</span>
                    </div>
                    <div x-show="isOperation" x-cloak class="grid gap-1.75">
                        <x-wire-select
                            id="bulk-dispatch-kit"
                            wire:model="form.kitId"
                            label="Kit despachado"
                            placeholder="Busca por funda o contenedor"
                            :async-data="['api' => route('intranet.kits.dispatched-options'), 'method' => 'POST']"
                            option-label="name"
                            option-value="id"
                            option-description="description"
                        />
                        <span class="text-sm text-ink-muted">Solo kits despachados cuya certificación sigue abierta.</span>
                    </div>
                    <p x-show="hasNoReason" class="m-0 pt-7 text-sm text-ink-muted">Elige el motivo: con Venta se pide el cliente y con Operación, el kit despachado.</p>
                </div>

                <div class="grid gap-1.75">
                    <x-wire-native-select id="bulk-dispatch-yard-zone" wire:model="form.yardZoneId" label="Patio de destino" :options="['' => 'Elige un patio'] + $yardZones->all()" option-key-value />
                    <span class="text-sm text-ink-muted">Zonas de patio de tu sede.</span>
                </div>
                <x-wire-textarea id="bulk-dispatch-notes" wire:model="form.notes" label="Observación (opcional)" rows="2" maxlength="{{ \App\Livewire\Forms\BulkDispatchForm::NOTES_MAX }}" />
            </div>

            <section class="flex flex-col gap-3.5 border-t border-line pt-4" aria-labelledby="bulk-dispatch-devices-title">
                <h4 id="bulk-dispatch-devices-title" class="m-0 font-heading text-base font-medium text-brand">Dispositivos</h4>

                <div role="tablist" class="flex gap-1 border-b border-line">
                    @foreach (['scan' => 'Escanear', 'range' => 'Por rango', 'quantity' => 'Por cantidad'] as $tab => $label)
                        <button
                            type="button"
                            role="tab"
                            data-tab="{{ $tab }}"
                            x-on:click="showTab"
                            x-bind:aria-selected="isTab('{{ $tab }}')"
                            x-bind:class="tabClass('{{ $tab }}')"
                            class="-mb-px min-h-11 flex-1 cursor-pointer border-0 border-b-3 bg-transparent px-1.5 text-sm whitespace-nowrap md:flex-none md:px-3.5 md:text-[0.9375rem]"
                        >{{ $label }}</button>
                    @endforeach
                </div>

                <div x-show="!isTab('quantity')" class="grid grid-cols-1 gap-1.75 md:max-w-[calc(50%-0.5rem)]">
                    <x-wire-native-select id="bulk-dispatch-filter-type" label="Tipo de dispositivo (opcional)" x-model="filterType" x-on:change="forgetPreview" :options="['' => 'Todos los tipos'] + $deviceTypes->all()" option-key-value />
                    <span class="text-sm text-ink-muted">Elígelo cuando la serie se repite en más de un tipo, como en los tipos sin serie.</span>
                </div>

                <div x-show="isTab('scan')" class="grid grid-cols-1 items-start gap-4 md:grid-cols-2">
                    <div class="grid gap-1.75">
                        <label for="bulk-dispatch-serial" class="text-sm font-medium text-ink">Serie del dispositivo</label>
                        <x-scan-input id="bulk-dispatch-serial" placeholder="Escanea o escribe y pulsa Enter" x-on:keydown.enter.prevent="scanDevice" />
                        <span class="text-sm text-ink-muted">Cada escaneo se valida y se agrega solo a la lista.</span>
                    </div>

                    <div wire:ignore class="overflow-hidden rounded-card border border-line bg-white">
                        <h5 class="m-0 bg-table-head px-3.5 py-2 font-sans text-xs font-medium tracking-wider text-ink-muted uppercase">Últimos escaneos</h5>
                        <p x-show="hasNoScans" class="m-0 px-3.5 py-2.5 text-sm text-ink-muted">Cada serie que escanees aparece aquí: en verde lo agregado y en rojo lo que no entró, con el motivo.</p>
                        <ul role="log" aria-live="polite" class="m-0 max-h-40 list-none overflow-y-auto p-0">
                            <template x-for="scan in scans" :key="scan.id">
                                <li class="flex items-center gap-2.5 border-b border-line px-3.5 py-2 text-sm last:border-b-0">
                                    @include('pages.kits.partials.scan-icon')
                                    <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                                        <strong class="font-semibold break-all" x-text="scan.serialNumber"></strong>
                                        <small class="text-[0.8125rem]" x-bind:class="scanMessageClass(scan)" x-text="scan.message"></small>
                                    </span>
                                    <flux:button size="sm" x-show="scan.state === 'failed'" x-on:click="retryScan(scan)" class="max-md:h-11">
                                        <i class="fa-solid fa-rotate-right text-sm" aria-hidden="true"></i>
                                        Reintentar
                                    </flux:button>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <div x-show="isTab('range')" x-cloak data-mode="range" x-on:keydown.enter="previewOnEnter" class="grid grid-cols-1 items-end gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
                    <x-wire-input id="bulk-dispatch-range-first" label="Serie inicial" x-model="rangeFirst" x-on:input="forgetPreview" autocomplete="off" maxlength="50" />
                    <x-wire-input id="bulk-dispatch-range-last" label="Serie final" x-model="rangeLast" x-on:input="forgetPreview" autocomplete="off" maxlength="50" />
                    <flux:button type="button" x-on:click="previewRange" wire:loading.attr="disabled" wire:target="byRange" class="max-md:h-11">Ver vista previa</flux:button>
                </div>

                <div x-show="isTab('quantity')" x-cloak data-mode="quantity" x-on:keydown.enter="previewOnEnter" class="grid grid-cols-1 items-end gap-4 md:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)_8.75rem_auto]">
                    <x-wire-native-select id="bulk-dispatch-type" label="Tipo de dispositivo" x-model="quantityType" x-on:change="forgetPreview" :options="['' => 'Elige un tipo'] + $deviceTypes->all()" option-key-value />
                    <x-wire-input id="bulk-dispatch-quantity-first" label="Serie inicial" x-model="quantityFirst" x-on:input="forgetPreview" autocomplete="off" maxlength="50" />
                    <div x-digits-only>
                        <x-wire-input id="bulk-dispatch-quantity" label="Cantidad" inputmode="numeric" x-model="quantity" x-on:input="forgetPreview" autocomplete="off" maxlength="5" />
                    </div>
                    <flux:button type="button" x-on:click="previewQuantity" wire:loading.attr="disabled" wire:target="byQuantity" class="max-md:h-11">Ver vista previa</flux:button>
                </div>
                <p x-show="isTab('quantity')" x-cloak class="m-0 -mt-1.5 text-sm text-ink-muted">Escribe la serie completa (HLS0000009) o solo su prefijo (HLS) para empezar por el primero disponible.</p>

                <div x-show="preview" x-cloak role="status" x-bind:class="previewClass" class="flex flex-wrap items-center gap-2 rounded-control p-3 text-sm leading-normal">
                    <span class="min-w-0 flex-1" x-text="preview?.message"></span>
                    <flux:button size="sm" variant="primary" x-show="canAdd" x-on:click="addPreviewed" wire:loading.attr="disabled" wire:target="byRange,byQuantity" class="max-md:h-11">
                        <span x-text="addLabel"></span>
                    </flux:button>
                </div>

                @error('form.deviceIds')
                    <ul role="alert" class="m-0 flex list-none flex-col gap-1 rounded-control bg-badge-danger p-3 text-sm text-badge-danger-ink">
                        @foreach ($errors->get('form.deviceIds') as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @enderror

                <div class="overflow-hidden rounded-card border border-line">
                    <div class="flex justify-between bg-table-head px-4 py-2 text-xs font-medium tracking-wider text-ink-muted uppercase">
                        <span>Dispositivos a despachar</span>
                        <span>{{ $deviceCount }} {{ $deviceCount === 1 ? 'dispositivo' : 'dispositivos' }}</span>
                    </div>
                    @if ($stretches === [])
                        <p class="m-0 px-4 py-5 text-center text-sm text-ink-muted">Aún no hay dispositivos. Escanéalos, agrégalos por rango o por cantidad.</p>
                    @else
                        <ul class="m-0 list-none p-0">
                            @foreach ($stretches as $stretch)
                                <li wire:key="stretch-{{ $stretch['key'] }}" class="flex items-center gap-3 border-t border-line px-4 py-2 text-sm even:bg-surface">
                                    <span class="flex min-w-0 flex-1 flex-col gap-0.5 md:grid md:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)_5rem] md:items-center md:gap-4">
                                        <strong class="font-medium">{{ $stretch['type'] }}</strong>
                                        <span class="break-all text-ink-muted md:text-ink">{{ $stretch['range'] }}</span>
                                        <span class="text-ink-muted md:text-right md:text-ink">{{ $stretch['count'] }}</span>
                                    </span>
                                    <button type="button" wire:click="removeStretch('{{ $stretch['key'] }}')" wire:loading.attr="disabled" wire:target="removeStretch" class="min-h-11 cursor-pointer border-0 bg-transparent p-0 text-sm text-danger md:min-h-7.5">Quitar</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>
        </div>

        <x-slot:footer>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="text-sm text-ink-muted">Solo dispositivos Disponibles y fuera de un kit. Al despachar pasan a Despachado.</span>
                <div class="flex w-full flex-col-reverse gap-3 md:w-auto md:flex-row">
                    <flux:button type="button" x-on:click="close" class="max-md:h-11">Cancelar</flux:button>
                    <flux:button variant="primary" type="button" x-on:click="confirmDispatch" wire:loading.attr="disabled" wire:target="confirmDispatch,save" class="max-md:h-11">
                        <i class="fa-solid fa-truck text-sm" aria-hidden="true"></i>
                        Despachar {{ $deviceCount }} {{ $deviceCount === 1 ? 'dispositivo' : 'dispositivos' }}
                    </flux:button>
                </div>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>

    @script
    <script>
        /**
         * The window «Despacho a granel» (H30) in the browser: the tabs, the client or the kit by the reason, the list of
         * the last scans and the preview of a range or a quantity. Each scan, preview and addition asks the server,
         * because only the server knows whether the devices can be dispatched; the list of devices to dispatch lives in
         * the server until the dispatch. Only a scan that could not reach the server offers "Reintentar".
         */
        const MAX_SCANS = 6;

        Alpine.data('bulkDispatch', () => ({
            tab: 'scan',
            scans: [],
            nextScanId: 1,
            rangeFirst: '',
            rangeLast: '',
            quantityType: '',
            filterType: '',
            quantityFirst: '',
            quantity: '',

            /**
             * The answer of the last preview or addition: { ok, message, count, mode }.
             */
            preview: null,

            get isSale() {
                return $wire.form.reason === 'sale';
            },

            get isOperation() {
                return $wire.form.reason === 'operation';
            },

            get hasNoReason() {
                return !this.isSale && !this.isOperation;
            },

            get hasNoScans() {
                return this.scans.length === 0;
            },

            get canAdd() {
                return this.preview?.ok === true && this.preview.mode !== 'added' && this.preview.count > 0;
            },

            get addLabel() {
                return `Agregar ${this.preview?.count ?? 0}`;
            },

            get previewClass() {
                return this.preview?.ok ? 'bg-badge-info text-badge-info-ink' : 'bg-badge-danger text-badge-danger-ink';
            },

            isTab(tab) {
                return this.tab === tab;
            },

            tabClass(tab) {
                return this.tab === tab ? 'border-highlight font-semibold text-brand' : 'border-transparent text-ink-muted';
            },

            showTab(event) {
                this.tab = event.currentTarget.dataset.tab;
                this.preview = null;
            },

            startDispatch() {
                this.tab = 'scan';
                this.scans = [];
                this.preview = null;
                this.rangeFirst = this.rangeLast = this.quantityType = this.quantityFirst = this.quantity = this.filterType = '';
                this.$nextTick(() => document.getElementById('bulk-dispatch-serial')?.focus());
            },

            /**
             * Forget what was chosen in a window closed without dispatching. Only asks the server when something was
             * chosen.
             */
            forgetDispatch() {
                const form = $wire.form;

                if (form.deviceIds.length > 0 || form.receiverId !== null || form.reason !== '' || $wire.$errors.any()) {
                    $wire.discard();
                }
            },

            chooseCreatedClient(event) {
                $wire.form.clientId = event.detail.clientId;
            },

            forgetPreview() {
                this.preview = null;
            },

            async previewRange() {
                await this.ask('range', false);
            },

            async previewQuantity() {
                await this.ask('quantity', false);
            },

            /**
             * Enter in a field of the range or the quantity shows the preview, as the button does.
             */
            async previewOnEnter(event) {
                if (event.target.tagName !== 'INPUT') {
                    return;
                }

                event.preventDefault();
                await this.ask(event.currentTarget.dataset.mode, false);
            },

            async addPreviewed() {
                await this.ask(this.preview.mode, true);
            },

            async ask(mode, add) {
                let answer;

                try {
                    answer = mode === 'range'
                        ? await $wire.byRange(this.rangeFirst, this.rangeLast, add, this.filterType)
                        : await $wire.byQuantity(this.quantityType, this.quantityFirst, this.quantity, add);
                } catch {
                    answer = { ok: false, message: 'Sin conexión: no se pudo consultar. Vuelve a intentarlo.', count: 0 };
                }

                this.preview = { ...answer, mode: add && answer.ok ? 'added' : mode };
            },

            confirmDispatch() {
                $wire.confirmDispatch();
            },

            askToDispatch(event) {
                confirmAction(event.detail.title, event.detail.text, () => $wire.save());
            },

            scanDevice(event) {
                const input = event.target;
                const serialNumber = String(input.value ?? '').trim().toUpperCase();

                input.value = '';

                if (serialNumber === '') {
                    return;
                }

                const scan = { id: this.nextScanId++, serialNumber, typeId: this.filterType, state: 'pending', message: 'Buscando…' };

                this.scans = [scan, ...this.scans].slice(0, MAX_SCANS);
                this.sendScan(scan.id, input);
            },

            retryScan(scan) {
                this.sendScan(scan.id, null);
            },

            scanMessageClass(scan) {
                return scan.state === 'added' || scan.state === 'pending' ? 'text-ink-muted' : 'text-badge-danger-ink';
            },

            async sendScan(scanId, input) {
                const scan = this.scans.find((item) => item.id === scanId);

                if (!scan) {
                    return;
                }

                scan.state = 'pending';
                scan.message = 'Buscando…';

                let result;

                try {
                    result = await $wire.scan(scan.serialNumber, scan.typeId);
                } catch {
                    result = undefined;
                }

                if (!result || typeof result.ok !== 'boolean') {
                    scan.state = 'failed';
                    scan.message = 'Sin conexión: no se pudo agregar.';
                } else {
                    scan.state = result.ok ? 'added' : 'rejected';
                    scan.message = result.message;
                }

                this.$nextTick(() => input?.focus());
            },
        }));
    </script>
    @endscript
</div>
