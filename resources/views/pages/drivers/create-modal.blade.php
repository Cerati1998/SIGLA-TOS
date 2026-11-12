<div>
    {{-- Drawn in the body: inside the drivers modal, the transform of its panel would misplace this modal. --}}
    @teleport('body')
        <div>
            <x-wire-modal-card
                title="Agregar conductor"
                name="driver-create"
                wire:model="form.openModal"
                x-on:open="$wire.form.documentNumber = ''; $wire.form.fullName = ''; $wire.form.license = ''; $wire.form.lookupStatus = null"
                x-on:close="($wire.$errors.any() || $wire.form.lookedUpDocument !== null) && $wire.clearErrors()"
            >
                <p class="m-0 mb-4 text-sm text-ink-muted">a {{ $carrierName }}</p>

                <form id="driver-create-form" wire:submit="save" class="grid gap-4">
                    <x-pages::drivers.fields id-prefix="driver-create" />
                </form>

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                        <flux:button variant="primary" type="submit" form="driver-create-form" wire:loading.attr="disabled" wire:target="save,lookup">Guardar</flux:button>
                    </div>
                </x-slot:footer>
            </x-wire-modal-card>
        </div>
    @endteleport
</div>
