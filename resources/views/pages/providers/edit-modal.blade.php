<div>
    <x-wire-modal-card title="Editar proveedor" wire:model="form.openModal">
        <form id="provider-edit-form" wire:submit="save" class="grid gap-4">
            <x-pages::providers.fields id-prefix="provider-edit" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="provider-edit-form" wire:loading.attr="disabled" wire:target="save,lookup">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
