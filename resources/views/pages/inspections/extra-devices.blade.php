<div x-data="extraDevices({ closed: @js($certification->isReadOnly()) })"
    x-on:open-extra-devices.window="openList"
    x-on:replace-security-device.window="startFromDevice($event.detail.deviceId)">

    <template x-teleport="body">
        <div x-show="listOpen" x-cloak x-on:keydown.escape.window="listOpen = false" x-on:click.self="listOpen = false"
            class="fixed inset-0 z-50 flex flex-col justify-end bg-ink/50 md:items-center md:justify-center md:p-6">
            <div role="dialog" aria-modal="true" aria-labelledby="extra-devices-title"
                class="flex max-h-[90vh] w-full flex-col gap-4 overflow-y-auto rounded-t-card bg-surface px-4 pt-3 pb-6 md:max-w-xl md:rounded-card md:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm text-ink-muted">{{ $certification->container }} · kit {{ $certification->kit->pouch_code }}</span>
                        <h3 id="extra-devices-title" class="m-0 font-heading text-h3 font-medium text-brand">Dispositivos extra</h3>
                    </div>
                    <button type="button" x-on:click="listOpen = false" class="inline-flex size-11 flex-none items-center justify-center" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
                    </button>
                </div>
                <p class="m-0 text-sm text-ink-muted">Bodega los despachó a granel, con motivo Operación, para esta unidad. Cada uno reemplaza a un dispositivo del kit del mismo tipo.</p>

                @if ($certification->isAnnulled())
                    <p class="m-0 flex items-start gap-2.5 rounded-control bg-badge-danger p-3.5 text-sm text-badge-danger-ink">
                        <x-feather name="slash" class="mt-0.5 size-4 shrink-0" />
                        <span>Se anuló la asignación de su kit: ya no admite reemplazos.</span>
                    </p>
                @elseif ($certification->isClosed())
                    <p class="m-0 flex items-start gap-2.5 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
                        <x-feather name="info" class="mt-0.5 size-4 shrink-0" />
                        <span>Esta certificación está cerrada. Para reemplazar un dispositivo, reapertúrala desde Asignaciones con su motivo.</span>
                    </p>
                @endif

                <template x-if="extras.length === 0">
                    <div class="flex flex-col items-center gap-2 px-2 py-5 text-center text-ink-muted">
                        <x-feather name="package" class="size-8" />
                        <span>Aún no hay dispositivos extra para esta unidad.</span>
                        <span class="text-sm">Pide a bodega un despacho a granel con motivo Operación para {{ $certification->container }}.</span>
                    </div>
                </template>

                <template x-if="unused.length > 0">
                    <span class="text-xs tracking-wider text-ink-muted uppercase" x-text="`Sin usar · ${unused.length}`"></span>
                </template>
                <template x-for="extra in unused" x-bind:key="extra.id">
                    <article class="flex flex-col gap-2.5 rounded-card bg-white px-4 py-3.5 shadow-card">
                        <div class="flex items-start justify-between gap-2.5">
                            <span class="flex min-w-0 flex-col gap-0.5">
                                <strong class="font-heading text-lg font-medium" x-text="extra.serialNumber"></strong>
                                <span class="text-sm text-ink-muted" x-text="extra.typeName"></span>
                            </span>
                            <x-status-badge variant="info">Sin usar</x-status-badge>
                        </div>
                        <span class="text-sm text-ink-muted" x-text="`${extra.dispatch} · despachado a ${extra.dispatchedTo} el ${extra.dispatchedAt}`"></span>
                        <button type="button" x-show="canReplace && placed.some((device) => device.typeId === extra.typeId)" x-on:click="startFromExtra(extra.id)"
                            class="inline-flex h-11 items-center justify-center rounded-control bg-brand px-3.5 text-base font-medium text-white">
                            Usar para reemplazar
                        </button>
                        <span x-show="canReplace && ! placed.some((device) => device.typeId === extra.typeId)" class="text-sm text-ink-muted">Ningún dispositivo de esta inspección es de este tipo.</span>
                    </article>
                </template>

                <template x-if="used.length > 0">
                    <span class="text-xs tracking-wider text-ink-muted uppercase" x-text="`Usados · ${used.length}`"></span>
                </template>
                <template x-for="extra in used" x-bind:key="extra.id">
                    <article class="flex flex-col gap-2.5 rounded-card bg-white px-4 py-3.5 shadow-card">
                        <div class="flex items-start justify-between gap-2.5">
                            <span class="flex min-w-0 flex-col gap-0.5">
                                <strong class="font-heading text-lg font-medium" x-text="extra.serialNumber"></strong>
                                <span class="text-sm text-ink-muted" x-text="extra.typeName"></span>
                            </span>
                            <x-status-badge variant="muted">Usado</x-status-badge>
                        </div>
                        <template x-if="extra.replaced !== null">
                            <span class="flex flex-col gap-1 text-sm">
                                <span>Reemplazó a <s class="text-ink-muted" x-text="extra.replaced.serialNumber"></s> en «<span x-text="extra.replaced.section"></span>»</span>
                                <span class="text-ink-muted" x-text="`${extra.dispatch} · el ${extra.replaced.at} por ${extra.replaced.by}`"></span>
                            </span>
                        </template>
                    </article>
                </template>

                <p x-show="extras.length > 0" class="m-0 text-sm text-ink-muted">Los extras que no se usen siguen en estado Despachado.</p>
                <flux:button x-on:click="listOpen = false" class="max-md:h-13">Volver a la sección</flux:button>
            </div>
        </div>
    </template>

    <div x-data="photoField('form.photo', 'form.thumbnail', () => removedSerial + ' · ' + @js(auth()->user()?->name ?? ''))">
        <x-wire-modal-card title="Reemplazar un dispositivo" wire:model="form.openModal" x-on:close="$wire.$errors.any() && $wire.clearErrors()">
            <form id="security-device-replacement-form" wire:submit="replace" class="grid gap-4">
                <div class="flex flex-col gap-1.5">
                    <label for="replacement-removed" class="text-sm font-medium">Dispositivo del kit que sale</label>
                    <template x-if="fixed === 'removed'">
                        <p class="m-0 rounded-control border border-line bg-surface px-3 py-2.5 text-base" x-text="removed === null ? '—' : `${removed.serialNumber} · ${removed.section}`"></p>
                    </template>
                    <template x-if="fixed !== 'removed'">
                        <x-wire-native-select id="replacement-removed" x-model.number="removedId" x-on:change="sync">
                            <option value="">Elige el dispositivo</option>
                            <template x-for="device in removableDevices" x-bind:key="device.id">
                                <option x-bind:value="device.id" x-text="`${device.serialNumber} · ${device.section}`"></option>
                            </template>
                        </x-wire-native-select>
                    </template>
                    <span class="text-sm text-ink-muted" x-text="installed === null ? 'Solo los dispositivos ya colocados en las secciones de esta inspección.' : `Solo los dispositivos de tipo ${installed.typeName} colocados en esta inspección.`"></span>
                    <flux:error name="form.removedDeviceId" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="replacement-installed" class="text-sm font-medium">Dispositivo extra que entra</label>
                    <template x-if="fixed === 'installed'">
                        <p class="m-0 rounded-control border border-line bg-surface px-3 py-2.5 text-base" x-text="installed === null ? '—' : `${installed.serialNumber} · ${installed.dispatch}`"></p>
                    </template>
                    <template x-if="fixed !== 'installed'">
                        <x-wire-native-select id="replacement-installed" x-model.number="installedId" x-on:change="sync">
                            <option value="">Elige el extra</option>
                            <template x-for="extra in availableExtras" x-bind:key="extra.id">
                                <option x-bind:value="extra.id" x-text="`${extra.serialNumber} · ${extra.dispatch}`"></option>
                            </template>
                        </x-wire-native-select>
                    </template>
                    <span class="text-sm text-ink-muted">Solo los extras sin usar del mismo tipo.</span>
                    <flux:error name="form.installedDeviceId" />
                </div>

                <div x-show="removed !== null && installed !== null" class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-2 rounded-card border border-line bg-surface p-3"
                    x-bind:aria-label="`Sale ${removedSerial}, entra ${installed?.serialNumber ?? ''}`">
                    <span class="flex min-w-0 flex-col gap-0.5">
                        <span class="text-xs tracking-wider text-ink-muted uppercase">Sale</span>
                        <strong class="font-heading text-base font-medium break-words text-badge-danger-ink" x-text="removedSerial"></strong>
                    </span>
                    <i class="fa-solid fa-arrow-right text-ink-muted" aria-hidden="true"></i>
                    <span class="flex min-w-0 flex-col gap-0.5">
                        <span class="text-xs tracking-wider text-ink-muted uppercase">Entra</span>
                        <strong class="font-heading text-base font-medium break-words text-badge-success-ink" x-text="installed?.serialNumber ?? ''"></strong>
                    </span>
                </div>

                <x-wire-textarea
                    id="replacement-reason"
                    wire:model="form.reason"
                    label="Motivo del reemplazo"
                    placeholder="Por ejemplo: se rompió el precinto al reabrir la puerta."
                    rows="3"
                    maxlength="{{ \App\Models\SecurityDeviceReplacement::REASON_MAX }}"
                    required
                />

                <x-photo-field label="Foto del dispositivo que sale" name="form.photo" title="Foto del dispositivo que sale" />
                <span class="-mt-2.5 text-sm text-ink-muted">Obligatoria. Queda como foto del descarte del dispositivo que sale.</span>

                <p role="note" class="m-0 flex gap-2.5 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
                    <x-feather name="alert-triangle" class="mt-0.5 size-4.5 shrink-0" />
                    <span>El dispositivo que sale pasa a <strong>Descartado</strong> con este motivo y esta foto. Después, la sección pide al menos una foto nueva con el extra colocado. <strong>El reemplazo no se deshace.</strong></span>
                </p>
            </form>

            <x-slot:footer>
                <div class="flex justify-end gap-3 max-md:flex-col-reverse">
                    <flux:button type="button" x-on:click="close" class="max-md:h-13">Cancelar</flux:button>
                    <flux:button variant="primary" type="submit" form="security-device-replacement-form" wire:loading.attr="disabled" wire:target="replace" x-bind:disabled="uploading" class="max-md:h-13">Reemplazar</flux:button>
                </div>
            </x-slot:footer>
        </x-wire-modal-card>
    </div>
</div>

@script
<script>
    Alpine.data('extraDevices', ({ closed }) => ({
        canReplace: ! closed && window.matchMedia('(pointer: coarse)').matches,
        listOpen: false,
        fixed: null,
        removedId: null,
        installedId: null,

        get extras() {
            return this.$wire.devices.extras;
        },

        get placed() {
            return this.$wire.devices.placed;
        },

        get unused() {
            return this.extras.filter((extra) => extra.unused);
        },

        get used() {
            return this.extras.filter((extra) => ! extra.unused);
        },

        get removed() {
            return this.placed.find((device) => device.id === this.removedId) ?? null;
        },

        get installed() {
            return this.extras.find((extra) => extra.id === this.installedId) ?? null;
        },

        get removedSerial() {
            return this.removed?.serialNumber ?? '';
        },

        get removableDevices() {
            return this.placed.filter((device) => device.typeId === this.installed?.typeId);
        },

        get availableExtras() {
            return this.unused.filter((extra) => extra.typeId === this.removed?.typeId);
        },

        openList() {
            this.listOpen = true;
        },

        startFromDevice(deviceId) {
            this.start(deviceId, null, 'removed');
        },

        startFromExtra(extraId) {
            this.listOpen = false;
            this.start(null, extraId, 'installed');
        },

        start(removedId, installedId, fixed) {
            if (! this.canReplace) {
                return;
            }

            this.fixed = fixed;
            this.removedId = removedId;
            this.installedId = installedId;

            if (fixed === 'removed' && this.availableExtras.length === 1) {
                this.installedId = this.availableExtras[0].id;
            }

            if (fixed === 'installed' && this.removableDevices.length === 1) {
                this.removedId = this.removableDevices[0].id;
            }

            this.$wire.form.reason = '';
            this.$wire.form.photo = null;
            this.$wire.form.thumbnail = null;
            this.sync();
            this.$wire.form.openModal = true;
        },

        sync() {
            this.$wire.form.removedDeviceId = this.removedId || null;
            this.$wire.form.installedDeviceId = this.installedId || null;
        },
    }));
</script>
@endscript
