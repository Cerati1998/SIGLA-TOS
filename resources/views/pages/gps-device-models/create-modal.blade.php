<div>
    <x-wire-modal-card
        title="Nuevo modelo GPS"
        name="gps-device-model-create"
        wire:model="form.openModal"
        x-on:open="$wire.form.brandId = null; $wire.form.name = ''; $wire.form.category = ''; $wire.form.openingType = ''; $wire.form.monitoringProductCode = ''; $wire.form.monitoringDeviceTypeCode = ''; $wire.form.description = ''"
        x-on:close="$wire.$errors.any() && $wire.clearErrors()"
    >
        <form id="gps-device-model-create-form" wire:submit="save" class="grid gap-4">
            <x-pages::gps-device-models.fields id-prefix="gps-device-model-create" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="gps-device-model-create-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
