<div>
    <x-wire-modal-card title="Editar tipo de dispositivo" wire:model="form.openModal">
        <form id="security-device-type-edit-form" wire:submit="save" class="grid gap-4">
            <x-wire-input id="security-device-type-edit-name" wire:model="form.name" label="Nombre" type="text" maxlength="100" required autofocus autocomplete="off" />
            <x-wire-input id="security-device-type-edit-kind" label="Tipo" type="text" :value="$form->kindLabel" readonly />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="security-device-type-edit-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
