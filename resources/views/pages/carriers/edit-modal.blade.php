<div>
    <x-wire-modal-card title="Editar transportista" wire:model="form.openModal">
        <form id="carrier-edit-form" wire:submit="save" class="grid gap-4">
            <x-pages::carriers.fields id-prefix="carrier-edit" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="carrier-edit-form" wire:loading.attr="disabled" wire:target="save,lookup">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
