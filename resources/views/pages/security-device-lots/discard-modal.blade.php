<div x-data="photoField('form.photo', 'form.thumbnail', () => $wire.form.serialNumber + ' · ' + @js(auth()->user()?->name ?? ''))">
    <x-wire-modal-card title="Descartar dispositivo" wire:model="form.openModal">
        <form id="security-device-discard-form" wire:submit="save" class="grid gap-4">
            <p role="note" class="m-0 flex gap-2.5 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
                <x-feather name="alert-triangle" class="mt-0.5 size-4.5 shrink-0" />
                <span><strong>{{ $form->serialNumber }}</strong> pasa a Descartado y ya no se agrega a un kit ni se despacha a granel. <strong>El descarte no se deshace.</strong></span>
            </p>

            <x-wire-textarea
                id="security-device-discard-reason"
                wire:model="form.reason"
                label="Motivo"
                placeholder="Por ejemplo: llegó roto en la recepción."
                rows="3"
                maxlength="{{ \App\Livewire\Forms\SecurityDeviceDiscardForm::REASON_MAX }}"
                required
            />

            <x-photo-field label="Foto" name="form.photo" :title="'Foto de '.$form->serialNumber" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="danger" type="submit" form="security-device-discard-form" wire:loading.attr="disabled" wire:target="save" x-bind:disabled="uploading">Descartar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
