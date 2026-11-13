<div>
    <x-wire-modal-card title="Editar zona de patio" wire:model="form.openModal">
        <form id="yard-zone-edit-form" wire:submit="save" class="grid gap-4">
            <x-pages::yard-zones.fields id-prefix="yard-zone-edit" :branches="$branches" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="yard-zone-edit-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
