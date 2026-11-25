{{-- How the inspector will see the section: a step of the certification, or a step of the checkpoint (H33). --}}
<aside class="flex flex-col gap-3" aria-label="Vista previa para el inspector">
    <div class="flex flex-col gap-2.5 rounded-card border border-line bg-surface p-3.5">
        <span class="text-xs font-medium tracking-wider text-ink-muted uppercase">Así lo verá el inspector</span>

        <div class="flex flex-col gap-2.5 rounded-card bg-white p-3.5 shadow-card">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[13px] text-ink-muted" x-text="isCheckpoint ? 'Paso del checkpoint' : 'Paso de la certificación'"></span>
                <x-status-badge variant="info" x-show="! isCheckpoint && typeLabels[$wire.form.type]" x-text="typeLabels[$wire.form.type]"></x-status-badge>
                <x-status-badge variant="info" x-show="isVerification" x-cloak>Verificación</x-status-badge>
            </div>

            <strong class="font-heading text-lg font-medium text-brand" x-text="$wire.form.name || 'Nombre de la sección'"></strong>

            <div x-show="! isCheckpoint && rows.length > 0" class="flex flex-col gap-1.5">
                <span class="text-[13px] font-medium text-ink">Coloca:</span>
                <template x-for="device in rows" x-bind:key="'preview-' + device.id">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="min-w-6.5 font-heading font-medium text-brand" x-text="(parseInt(device.quantity) || 0) + ' ×'"></span>
                        <span x-text="nameOf(find(device.id))"></span>
                    </div>
                </template>
            </div>

            <div x-show="isVerification" x-cloak class="flex flex-col gap-1.5 text-sm">
                <span x-show="links.length > 0" x-text="'Las series de ' + links.map((id) => findSection(id).name).join(', ') + ', una por una, con «Correcta» e «Incorrecta».'"></span>
                <span x-show="links.length === 0" class="text-ink-muted">Elige las secciones que verifica.</span>
            </div>

            <div x-show="! isVerification" class="flex items-center gap-2 text-sm text-badge-info-ink">
                <x-feather name="camera" class="size-4.5" />
                Toma al menos una foto
            </div>
        </div>
    </div>

    <p class="flex gap-2 rounded-control bg-badge-info p-3 text-sm text-badge-info-ink">
        <x-feather name="info" class="size-4.5 shrink-0" />
        <span x-text="isCheckpoint ? 'Las secciones de checkpoint no llevan dispositivos propios. Los cambios valen para checkpoints nuevos.' : 'Los cambios valen para certificaciones nuevas. Las ya cerradas no cambian.'"></span>
    </p>
</aside>
