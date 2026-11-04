<div>
    <x-wire-modal-card title="Editar sucursal" wire:model="form.openModal">
        <form id="branch-edit-form" wire:submit="save" class="grid gap-4">
            <x-pages::branches.fields id-prefix="branch-edit" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="branch-edit-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
