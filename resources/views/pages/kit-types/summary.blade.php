<aside class="flex flex-col gap-3" aria-label="Resumen del tipo de kit">
    <div class="flex flex-col gap-3 rounded-card border border-line bg-surface p-4">
        <h4 class="font-heading text-base font-medium text-brand">Resumen</h4>

        <div class="flex items-baseline gap-2">
            <span class="font-heading text-4xl leading-none font-medium text-brand" x-text="total"></span>
            <span class="text-sm text-ink-muted" x-text="(total === 1 ? 'dispositivo' : 'dispositivos') + ' en ' + selected.length + (selected.length === 1 ? ' tipo' : ' tipos')"></span>
        </div>

        <div x-show="selected.length > 0" class="flex flex-col gap-1.5">
            <template x-for="device in selected" x-bind:key="device.id">
                <div class="flex justify-between gap-2 text-sm">
                    <span x-text="device.name"></span>
                    <span class="font-heading font-medium text-brand" x-text="'× ' + device.quantity"></span>
                </div>
            </template>
        </div>

        <p x-show="selected.length === 0" class="flex gap-2 rounded-control bg-badge-warning p-2.5 text-sm text-badge-warning-ink">
            <x-feather name="alert-triangle" class="size-4.5 shrink-0" />
            Enciende al menos un dispositivo.
        </p>
    </div>

    <div x-show="selected.length > 0" class="flex flex-col gap-3 rounded-card border border-line bg-white p-4">
        <h4 class="font-heading text-base font-medium text-brand">Así lo verá bodega</h4>
        <p class="-mt-1.5 text-sm text-ink-muted">Al armar un kit de este tipo, cada barra avanza con lo que escanea.</p>

        <template x-for="device in selected" x-bind:key="'preview-' + device.id">
            <div class="flex flex-col gap-1">
                <div class="flex justify-between gap-2 text-[13px]">
                    <span x-text="device.name"></span>
                    <span class="text-ink-muted" x-text="'0 de ' + device.quantity"></span>
                </div>
                <div class="h-1.5 rounded-full bg-line"></div>
            </div>
        </template>
    </div>

    <p class="flex gap-2 rounded-control bg-badge-info p-3 text-sm text-badge-info-ink">
        <x-feather name="info" class="size-4.5 shrink-0" />
        Si lo editas después, los kits ya armados no cambian.
    </p>
</aside>
