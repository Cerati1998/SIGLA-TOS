<div x-data="photoField('form.photo', 'form.thumbnail', () => $wire.form.code || 'Dispositivo GPS')">
    <x-wire-modal-card
        title="Nuevo dispositivo GPS"
        name="gps-device-create"
        wire:model="form.openModal"
        x-on:open="forget(); $wire.form.code = ''; $wire.form.monitoringAssetId = ''; $wire.form.gpsDeviceModelId = null; $wire.form.ownership = '{{ \App\Enums\GpsDeviceOwnership::Own->value }}'; $wire.form.rentalProviderId = null; $wire.form.branchId = ''; $wire.form.notes = ''; $wire.form.photo = null; $wire.form.thumbnail = null"
        x-on:close="$wire.$errors.any() && $wire.clearErrors()"
    >
        <form id="gps-device-create-form" wire:submit="save" class="grid gap-4">
            <x-pages::gps-devices.fields id-prefix="gps-device-create" :branches="$form->branches()" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="gps-device-create-form" wire:loading.attr="disabled" wire:target="save" x-bind:disabled="uploading">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
