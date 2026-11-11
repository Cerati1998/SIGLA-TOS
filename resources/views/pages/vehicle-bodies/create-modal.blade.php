<div>
    <x-wire-modal-card
        title="Nueva carrocería"
        name="vehicle-body-create"
        wire:model="form.openModal"
        x-on:open="$wire.form.name = ''"
        x-on:close="$wire.$errors.any() && $wire.clearErrors()"
    >
        <form id="vehicle-body-create-form" wire:submit="save" class="grid gap-4">
            <x-wire-input id="vehicle-body-create-name" wire:model="form.name" label="Nombre" description="Se guarda en mayúsculas." type="text" maxlength="100" required autocomplete="off" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="vehicle-body-create-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
