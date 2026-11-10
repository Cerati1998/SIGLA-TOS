<div>
    <x-wire-modal-card
        title="Nueva sección"
        name="certification-section-create"
        wire:model="form.openModal"
        x-on:open="$wire.form.name = ''; $wire.form.type = ''"
        x-on:close="$wire.$errors.any() && $wire.clearErrors()"
    >
        <form id="certification-section-create-form" wire:submit="save" class="grid gap-4">
            @include('pages.certification-sections.fields', ['prefix' => 'certification-section-create'])
            <span class="text-sm text-ink-muted">La sección nueva queda al final del orden.</span>
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="certification-section-create-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
