@php
    $isFiltered = $kitTypeFilter !== '' || $shippingLineFilter !== '' || trim($pouchSearch) !== '' || $onlyMarked;
    $pageKitTypes = $kits->mapWithKeys(fn ($kit) => [$kit->id => $kit->kitType->name])->all();
    $firstShown = $total === 0 ? 0 : ($page - 1) * \App\Livewire\KitDispatches\CreateModal::PER_PAGE + 1;
    $lastShown = min($total, $page * \App\Livewire\KitDispatches\CreateModal::PER_PAGE);
@endphp

<div x-data="kitDispatch" x-on:confirm-kit-dispatch.window="askToDispatch">
    <x-wire-modal-card
        title="Despacho masivo de kits"
        name="kit-dispatch-create"
        width="sm:max-w-5xl"
        wire:model="form.openModal"
        x-on:open="startDispatch"
        x-on:close="forgetDispatch"
    >
        <div class="flex flex-col gap-4.5">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="grid gap-1.75">
                    <x-wire-select
                        id="kit-dispatch-receiver"
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
                    <x-wire-input id="kit-dispatch-deliverer" label="Quién entrega" :value="$delivererName" disabled />
                    <span class="text-sm text-ink-muted">Eres tú, el usuario que despacha. Queda registrado solo.</span>
                </div>
            </div>

            <section class="flex flex-col gap-3.5 border-t border-line pt-4" aria-labelledby="kit-dispatch-kits-title">
                <h4 id="kit-dispatch-kits-title" class="m-0 font-heading text-base font-medium text-brand">Kits a despachar</h4>

                <div class="grid grid-cols-1 items-start gap-4 md:grid-cols-2">
                    <div class="grid gap-1.75">
                        <label for="kit-dispatch-pouch" class="text-sm font-medium text-ink">Funda del kit</label>
                        <x-scan-input id="kit-dispatch-pouch" placeholder="Escanea o escribe y pulsa Enter" x-on:keydown.enter.prevent="scanPouch" />
                        <span class="text-sm text-ink-muted">Escanea la funda y el kit se marca solo en la tabla, o márcalo con su casilla.</span>
                    </div>

                    <div wire:ignore class="overflow-hidden rounded-card border border-line bg-white">
                        <h5 class="m-0 bg-table-head px-3.5 py-2 font-sans text-xs font-medium tracking-wider text-ink-muted uppercase">Últimos escaneos</h5>
                        <p x-show="hasNoScans" class="m-0 px-3.5 py-2.5 text-sm text-ink-muted">Cada funda que escanees aparece aquí: en verde lo marcado y en rojo lo que no entró, con el motivo.</p>
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

                <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto]">
                    <x-wire-native-select id="kit-dispatch-kit-type" wire:model.live="kitTypeFilter" label="Tipo de kit" :options="['' => 'Todos'] + $kitTypes->all()" option-key-value />
                    <x-wire-native-select id="kit-dispatch-shipping-line" wire:model.live="shippingLineFilter" label="Naviera" :options="['' => 'Todas'] + $shippingLines->all()" option-key-value />
                    <x-wire-input id="kit-dispatch-pouch-search" wire:model.live.debounce.400ms="pouchSearch" label="Funda" type="search" placeholder="Buscar por funda" autocomplete="off" />
                    <label for="kit-dispatch-only-marked" class="inline-flex min-h-11 cursor-pointer items-center gap-2 text-sm md:min-h-8.5">
                        <input id="kit-dispatch-only-marked" type="checkbox" wire:model.live="onlyMarked" class="size-4.5 accent-brand">
                        Solo marcados
                    </label>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge variant="muted">{{ $availableCount }} {{ $availableCount === 1 ? 'armado disponible' : 'armados disponibles' }}</x-status-badge>
                    <x-status-badge variant="info"><span x-text="markedLabel">{{ $markedCount }} {{ $markedCount === 1 ? 'marcado' : 'marcados' }}</span></x-status-badge>
                    <span x-show="markedSummary" x-text="markedSummary" class="text-sm text-ink-muted">{{ $markedSummary }}</span>
                </div>

                @error('form.kitIds')
                    <ul role="alert" class="m-0 flex list-none flex-col gap-1 rounded-control bg-badge-danger p-3 text-sm text-badge-danger-ink">
                        @foreach ($errors->get('form.kitIds') as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @enderror

                <div class="overflow-hidden rounded-card border border-line">
                    @if ($kits->isEmpty())
                        <p role="status" class="m-0 flex gap-2.5 bg-badge-info p-3.5 text-sm leading-normal text-badge-info-ink">
                            <x-feather name="info" class="mt-0.5 size-4.5 flex-none" />
                            <span>{{ $isFiltered ? 'No hay kits Armados sin despachar con estos filtros.' : 'No hay kits Armados sin despachar. Los kits se arman en Bodega → Kits.' }}</span>
                        </p>
                    @else
                        <ul class="m-0 list-none p-0 md:hidden">
                            @foreach ($kits as $kit)
                                <li wire:key="kit-card-{{ $kit->id }}" class="border-b border-line last:border-b-0">
                                    <label for="kit-dispatch-card-{{ $kit->id }}" class="flex min-h-14 cursor-pointer items-center gap-3 px-4 py-2.5 has-checked:bg-badge-info">
                                        <input id="kit-dispatch-card-{{ $kit->id }}" type="checkbox" wire:model="form.kitIds" value="{{ $kit->id }}" data-kit-type="{{ $kit->kitType->name }}" x-on:change="rememberKitType" class="size-5.5 flex-none accent-brand">
                                        <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                                            <strong class="text-base font-semibold">{{ $kit->pouch_code }}</strong>
                                            <small class="text-[0.8125rem] text-ink-muted">{{ $kit->kitType->name }} · {{ $kit->shippingLine->code }} · {{ $kit->week() }} · {{ $kit->getAttribute('devices_count') }}</small>
                                        </span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>

                        <div class="overflow-x-auto max-md:hidden">
                            <table class="w-full border-collapse text-sm">
                                <thead>
                                    <tr class="bg-table-head text-left text-xs font-medium tracking-wider text-ink-muted uppercase">
                                        <th class="border-b border-line px-4 py-2.5">
                                            <input
                                                type="checkbox"
                                                wire:key="kit-dispatch-page-{{ implode('-', array_keys($pageKitTypes)) }}"
                                                data-kit-types="{{ json_encode($pageKitTypes, JSON_FORCE_OBJECT) }}"
                                                x-bind:checked="isPageMarked($el)"
                                                x-on:change="togglePage"
                                                aria-label="Marcar todos los de esta página"
                                                class="size-4.5 align-middle accent-brand"
                                            >
                                        </th>
                                        <th class="border-b border-line px-4 py-2.5 font-medium">Funda</th>
                                        <th class="border-b border-line px-4 py-2.5 font-medium">Tipo de kit</th>
                                        <th class="border-b border-line px-4 py-2.5 font-medium">Naviera</th>
                                        <th class="border-b border-line px-4 py-2.5 font-medium">Semana</th>
                                        <th class="border-b border-line px-4 py-2.5 text-right font-medium">Dispositivos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($kits as $kit)
                                        <tr wire:key="kit-row-{{ $kit->id }}" class="border-b border-line last:border-b-0 even:bg-surface has-checked:bg-badge-info">
                                            <td class="px-4 py-2.5">
                                                <input id="kit-dispatch-row-{{ $kit->id }}" type="checkbox" wire:model="form.kitIds" value="{{ $kit->id }}" data-kit-type="{{ $kit->kitType->name }}" x-on:change="rememberKitType" aria-label="Marcar {{ $kit->pouch_code }}" class="size-4.5 align-middle accent-brand">
                                            </td>
                                            <td class="px-4 py-2.5 whitespace-nowrap"><label for="kit-dispatch-row-{{ $kit->id }}" class="cursor-pointer font-semibold">{{ $kit->pouch_code }}</label></td>
                                            <td class="px-4 py-2.5 whitespace-nowrap">{{ $kit->kitType->name }}</td>
                                            <td class="px-4 py-2.5">{{ $kit->shippingLine->code }}</td>
                                            <td class="px-4 py-2.5 whitespace-nowrap">{{ $kit->week() }}</td>
                                            <td class="px-4 py-2.5 text-right">{{ $kit->getAttribute('devices_count') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line px-4 py-2 text-sm text-ink-muted">
                            <span>Mostrando {{ $firstShown }} a {{ $lastShown }} de {{ $total }} · lo marcado se conserva al cambiar de página o de filtro</span>
                            <div class="flex gap-2">
                                <flux:button size="sm" wire:click="previousPage" wire:loading.attr="disabled" wire:target="previousPage" :disabled="$page <= 1" class="max-md:h-11">Anterior</flux:button>
                                <flux:button size="sm" wire:click="nextPage" wire:loading.attr="disabled" wire:target="nextPage" :disabled="$page >= $lastPage" class="max-md:h-11">Siguiente</flux:button>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <x-slot:footer>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="text-sm text-ink-muted">Solo kits Armados y sin despachar. Al despachar, su contenido ya no cambia.</span>
                <div class="flex w-full flex-col-reverse gap-3 md:w-auto md:flex-row">
                    <flux:button type="button" x-on:click="close" class="max-md:h-11">Cancelar</flux:button>
                    <flux:button variant="primary" type="button" x-on:click="confirmDispatch" wire:loading.attr="disabled" wire:target="confirmDispatch,save" class="max-md:h-11">
                        <i class="fa-solid fa-truck text-sm" aria-hidden="true"></i>
                        <span x-text="dispatchLabel">Despachar {{ $markedCount }} {{ $markedCount === 1 ? 'kit' : 'kits' }}</span>
                    </flux:button>
                </div>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>

    @script
    <script>
        /**
         * The window «Despacho masivo de kits» (H15) in the browser. Marking kits with their checkbox, a whole page at
         * once, and counting and summarizing what is marked happen here, without asking the server: the marked kits
         * travel with the next request (a scan, a filter, a page or the dispatch), and the server checks them again
         * before dispatching. Each scanned pouch is sent to the server by itself, because only the server knows whether
         * that kit can be dispatched; the list of the last scans shows what was marked and what was not, with the reason.
         * Only a scan that could not reach the server (no connection or a server error) offers "Reintentar".
         */
        const MAX_SCANS = 6;

        const plural = (count, singular, plural) => `${count} ${count === 1 ? singular : plural}`;

        Alpine.data('kitDispatch', () => ({
            scans: [],
            nextScanId: 1,

            /**
             * The kit type of each marked kit, by its id, to summarize what is marked.
             */
            kitTypes: {},

            get markedIds() {
                return [...new Set($wire.form.kitIds.map(Number))];
            },

            get markedLabel() {
                return plural(this.markedIds.length, 'marcado', 'marcados');
            },

            get dispatchLabel() {
                return 'Despachar ' + plural(this.markedIds.length, 'kit', 'kits');
            },

            /**
             * How many kits of each kit type are marked, as "2 Kit reefer · 1 Kit seco", like the PDF of the dispatch.
             */
            get markedSummary() {
                const counts = {};

                this.markedIds
                    .map((kitId) => this.kitTypes[kitId])
                    .filter((kitType) => kitType !== undefined)
                    .forEach((kitType) => (counts[kitType] = (counts[kitType] ?? 0) + 1));

                return Object.keys(counts)
                    .sort()
                    .map((kitType) => `${counts[kitType]} ${kitType}`)
                    .join(' · ');
            },

            get hasNoScans() {
                return this.scans.length === 0;
            },

            startDispatch() {
                this.scans = [];
                this.kitTypes = {};
                this.$nextTick(() => document.getElementById('kit-dispatch-pouch')?.focus());
            },

            /**
             * Forget what was chosen in a window closed without dispatching. Only asks the server when something was
             * chosen.
             */
            forgetDispatch() {
                if (this.markedIds.length > 0 || $wire.form.receiverId !== null || $wire.$errors.any()) {
                    $wire.discard();
                }
            },

            rememberKitType(event) {
                this.kitTypes[event.target.value] = event.target.dataset.kitType;
            },

            isPageMarked(checkbox) {
                const pageIds = Object.keys(JSON.parse(checkbox.dataset.kitTypes)).map(Number);

                return pageIds.length > 0 && pageIds.every((kitId) => this.markedIds.includes(kitId));
            },

            /**
             * Mark or unmark at once the kits of the page the table shows.
             */
            togglePage(event) {
                const pageKitTypes = JSON.parse(event.target.dataset.kitTypes);
                const pageIds = Object.keys(pageKitTypes).map(Number);
                const otherIds = this.markedIds.filter((kitId) => !pageIds.includes(kitId));

                Object.assign(this.kitTypes, pageKitTypes);
                $wire.form.kitIds = event.target.checked ? [...otherIds, ...pageIds] : otherIds;
            },

            confirmDispatch() {
                $wire.confirmDispatch();
            },

            askToDispatch(event) {
                confirmAction(event.detail.title, event.detail.text, () => $wire.save());
            },

            scanPouch(event) {
                const input = event.target;
                const pouchCode = String(input.value ?? '').trim().toUpperCase();

                input.value = '';

                if (pouchCode === '') {
                    return;
                }

                const scan = { id: this.nextScanId++, serialNumber: pouchCode, state: 'pending', message: 'Buscando…' };

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
                    result = await $wire.mark(scan.serialNumber);
                } catch {
                    result = undefined;
                }

                if (!result || typeof result.ok !== 'boolean') {
                    scan.state = 'failed';
                    scan.message = 'Sin conexión: no se pudo marcar.';
                } else {
                    scan.state = result.ok ? 'added' : 'rejected';
                    scan.message = result.message;

                    if (result.kitId) {
                        this.kitTypes[result.kitId] = result.kitType;
                    }
                }

                this.$nextTick(() => input?.focus());
            },
        }));
    </script>
    @endscript
</div>
