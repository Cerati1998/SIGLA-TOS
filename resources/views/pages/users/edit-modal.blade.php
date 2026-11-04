<div>
    <x-wire-modal-card title="Editar usuario" wire:model="form.openModal">
        <form id="user-edit-form" wire:submit="save" class="grid gap-4">
            <x-pages::users.fields id-prefix="user-edit" :identities="$form->identities()" :branches="$form->branches()" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="user-edit-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
