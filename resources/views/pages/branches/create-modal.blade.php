<div>
    <x-wire-modal-card
        title="Nueva sucursal"
        name="branch-create"
        wire:model="form.openModal"
        x-on:open="$wire.form.name = ''; $wire.form.ubigeo = ''; $wire.form.address = ''; $wire.form.phone = ''; $wire.form.email = ''; $wire.form.website = ''"
        x-on:close="$wire.$errors.any() && $wire.clearErrors()"
    >
        <form id="branch-create-form" wire:submit="save" class="grid gap-4">
            <span class="text-sm text-ink-muted">El código se asigna solo al guardar.</span>

            <x-pages::branches.fields id-prefix="branch-create" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="branch-create-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
