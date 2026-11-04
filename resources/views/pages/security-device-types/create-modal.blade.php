<div>
    <x-wire-modal-card
        title="Nuevo tipo de dispositivo"
        name="security-device-type-create"
        wire:model="form.openModal"
        x-on:open="$wire.form.name = ''; $wire.form.kind = ''"
        x-on:close="$wire.$errors.any() && $wire.clearErrors()"
    >
        <form id="security-device-type-create-form" wire:submit="save" class="grid gap-4">
            <x-wire-input id="security-device-type-create-name" wire:model="form.name" label="Nombre" type="text" maxlength="100" required autofocus autocomplete="off" />
            <x-wire-native-select id="security-device-type-create-kind" wire:model="form.kind" label="Tipo" placeholder="Selecciona" :options="$kinds" option-key-value required />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="security-device-type-create-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
