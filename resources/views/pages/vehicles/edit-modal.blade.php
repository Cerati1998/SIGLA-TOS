<div>
    <x-wire-modal-card title="Editar vehículo" wire:model="form.openModal">
        <form id="vehicle-edit-form" wire:submit="save" class="grid gap-4">
            <x-pages::vehicles.fields id-prefix="vehicle-edit" :kinds="$kinds" :bodies="$bodies" :categories="$categories" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="vehicle-edit-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
