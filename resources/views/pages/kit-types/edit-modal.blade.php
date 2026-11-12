<div>
    <x-wire-modal-card title="Editar tipo de kit" width="sm:max-w-5xl" wire:model="form.openModal">
        <form id="kit-type-edit-form" wire:submit="save">
            <x-pages::kit-types.fields id-prefix="kit-type-edit" :container-types="$containerTypes" :device-types="$deviceTypes" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="kit-type-edit-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
