<div>
    <x-wire-modal-card
        title="Nuevo tipo de kit"
        name="kit-type-create"
        width="sm:max-w-5xl"
        wire:model="form.openModal"
        x-on:open="$wire.form.name = ''; $wire.form.containerTypeId = ''; Object.values($wire.form.devices).forEach((device) => { device.on = false; device.quantity = 1 })"
        x-on:close="$wire.$errors.any() && $wire.clearErrors()"
    >
        <form id="kit-type-create-form" wire:submit="save">
            <x-pages::kit-types.fields id-prefix="kit-type-create" :container-types="$containerTypes" :device-types="$deviceTypes" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="kit-type-create-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
