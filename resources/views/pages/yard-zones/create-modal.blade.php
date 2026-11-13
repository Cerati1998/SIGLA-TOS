<div>
    <x-wire-modal-card
        title="Nueva zona de patio"
        name="yard-zone-create"
        wire:model="form.openModal"
        x-on:open="$wire.form.branchId = ''; $wire.form.name = ''; $wire.form.capacity = ''"
        x-on:close="$wire.$errors.any() && $wire.clearErrors()"
    >
        <form id="yard-zone-create-form" wire:submit="save" class="grid gap-4">
            <x-pages::yard-zones.fields id-prefix="yard-zone-create" :branches="$branches" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="yard-zone-create-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
