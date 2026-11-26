<div>
    <x-wire-modal-card
        title="Iniciar la reinspección"
        name="reinspection-start"
        wire:model="form.openModal"
        x-on:open="$wire.form.reason = ''"
        x-on:close="$wire.$errors.any() && $wire.clearErrors()"
    >
        <form id="reinspection-start-form" wire:submit="start" class="grid gap-3.5">
            <p class="m-0 text-base">
                El checkpoint de <strong>{{ $checkpoint->certification->container }}</strong> no se cierra. Se {{ count($sections) === 1 ? 'reabre' : 'reabren' }} en la certificación solo {{ count($sections) === 1 ? 'la sección' : 'las secciones' }} donde {{ count($sections) === 1 ? 'quedó' : 'quedaron' }} las series incorrectas:
            </p>

            <ul class="m-0 flex list-none flex-col gap-2 rounded-lg bg-surface px-3 py-2.5 text-sm">
                @foreach ($sections as $section)
                    <li wire:key="reopened-{{ $loop->index }}" class="flex flex-col gap-0.5">
                        <span class="flex items-center justify-between gap-2">
                            <strong class="font-heading text-[15px] font-medium">{{ $section['section'] }}</strong>
                            <x-status-badge :variant="$section['type'] === 'Interna' ? 'purple' : 'info'" class="text-xs">{{ $section['type'] }}</x-status-badge>
                        </span>
                        <span>{{ count($section['series']) === 1 ? 'Serie incorrecta' : 'Series incorrectas' }}: {{ implode(', ', $section['series']) }}</span>
                    </li>
                @endforeach
            </ul>

            <x-wire-textarea id="reinspection-start-reason" wire:model="form.reason" label="Motivo" rows="4"
                placeholder="Por ejemplo: la cinta del evaporador 3 está rota." maxlength="{{ \App\Livewire\Forms\ReinspectionForm::REASON_MAX }}" required />

            <p class="m-0 text-[13px] text-ink-muted">Bodega despacha a granel, por Operación, los dispositivos nuevos para este contenedor. El inspector certificado los coloca y vuelve a tomar las fotos.</p>
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button type="submit" form="reinspection-start-form" wire:loading.attr="disabled" wire:target="start" class="bg-highlight! text-white! border-0!">Iniciar reinspección</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
