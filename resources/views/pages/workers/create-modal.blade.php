<div>
    <x-wire-modal-card
        title="Nuevo trabajador"
        name="worker-create"
        wire:model="form.openModal"
        x-on:open="$wire.form.identityId = '{{ \App\Models\Identity::DNI }}'; $wire.form.documentNumber = ''; $wire.form.fullName = ''; $wire.form.origin = '{{ \App\Livewire\Forms\WorkerForm::OWN }}'; $wire.form.position = ''; $wire.form.providerId = null; $wire.form.lookupStatus = null"
        x-on:close="($wire.$errors.any() || $wire.form.lookedUpDocument !== null) && $wire.clearErrors()"
    >
        <form id="worker-create-form" wire:submit="save" class="grid gap-4">
            <x-pages::workers.fields id-prefix="worker-create" :identities="$form->identities()" lookup />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="worker-create-form" wire:loading.attr="disabled" wire:target="save,lookup">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
