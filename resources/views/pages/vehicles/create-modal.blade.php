<div>
    <x-wire-modal-card
        title="Nuevo vehículo"
        name="vehicle-create"
        wire:model="form.openModal"
        x-on:open="$wire.form.plate = ''; $wire.form.brand = ''; $wire.form.kind = '{{ \App\Enums\VehicleKind::Tractor->value }}'; $wire.form.vehicleBodyId = ''; $wire.form.category = '{{ \App\Enums\VehicleCategory::N3->value }}'; $wire.form.color = ''"
        x-on:close="$wire.$errors.any() && $wire.clearErrors()"
    >
        <form id="vehicle-create-form" wire:submit="save" class="grid gap-4">
            <x-pages::vehicles.fields id-prefix="vehicle-create" :kinds="$kinds" :bodies="$bodies" :categories="$categories" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="vehicle-create-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
