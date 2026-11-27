{{-- On opening, the photo the device already has is shown; removing it marks it to be forgotten when saving. --}}
<div
    x-data="photoField('form.photo', 'form.thumbnail', () => $wire.form.code || 'Dispositivo GPS')"
    x-init="$watch(() => $wire.form.openModal, (open) => open && ($wire.form.savedThumbnailUrl ? showSaved($wire.form.savedPhotoUrl, $wire.form.savedThumbnailUrl) : forget()))"
    x-on:photo-removed="$wire.form.removePhoto = true"
>
    <x-wire-modal-card title="Editar dispositivo GPS" wire:model="form.openModal">
        <form id="gps-device-edit-form" wire:submit="save" class="grid gap-4">
            <x-pages::gps-devices.fields id-prefix="gps-device-edit" :branches="$form->branches()" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="gps-device-edit-form" wire:loading.attr="disabled" wire:target="save" x-bind:disabled="uploading">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
