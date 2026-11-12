<div>
    <x-wire-modal-card title="Editar sección" width="sm:max-w-4xl" wire:model="form.openModal">
        <form id="certification-section-edit-form" wire:submit="save">
            @include('pages.certification-sections.fields', ['prefix' => 'certification-section-edit'])
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="certification-section-edit-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
