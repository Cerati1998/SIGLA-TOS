<div>
    <x-wire-modal-card
        title="Nueva línea naviera"
        name="shipping-line-create"
        wire:model="form.openModal"
        x-on:open="$wire.form.code = ''; $wire.form.name = ''"
        x-on:close="$wire.$errors.any() && $wire.clearErrors()"
    >
        <form id="shipping-line-create-form" wire:submit="save" class="grid gap-4">
            <x-pages::shipping-lines.fields id-prefix="shipping-line-create" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="shipping-line-create-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
