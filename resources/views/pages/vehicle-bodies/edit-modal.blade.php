<div>
    <x-wire-modal-card title="Editar carrocería" wire:model="form.openModal">
        <form id="vehicle-body-edit-form" wire:submit="save" class="grid gap-4">
            <x-wire-input id="vehicle-body-edit-name" wire:model="form.name" label="Nombre" description="Se guarda en mayúsculas." type="text" maxlength="100" required autocomplete="off" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="vehicle-body-edit-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
