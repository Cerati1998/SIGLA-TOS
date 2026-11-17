<div>
    <x-wire-modal-card
        title="Nuevo cliente"
        name="client-create"
        wire:model="form.openModal"
        x-on:open="$wire.form.identityId = '{{ \App\Models\Identity::RUC }}'; $wire.form.documentNumber = ''; $wire.form.businessName = ''; $wire.form.address = ''; $wire.form.email = ''; $wire.form.phone = ''; $wire.form.paymentCondition = '{{ \App\Enums\PaymentCondition::Cash->value }}'; $wire.form.creditDays = ''; $wire.form.lookupStatus = null"
        x-on:close="($wire.$errors.any() || $wire.form.lookedUpDocument !== null) && $wire.clearErrors()"
    >
        <form id="client-create-form" wire:submit="save" class="grid gap-4">
            <x-pages::clients.fields id-prefix="client-create" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="client-create-form" wire:loading.attr="disabled" wire:target="save,lookup">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
