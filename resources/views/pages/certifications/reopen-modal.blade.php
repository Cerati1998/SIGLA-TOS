<div>
    <x-wire-modal-card :title="'Reaperturar '.$form->container" wire:model="form.openModal">
        <form id="certification-reopen-form" wire:submit="reopen" class="grid gap-4">
            <p class="m-0 text-base text-ink-muted">Se podrán corregir o sumar fotos y después cerrarla otra vez. El kit y sus dispositivos siguen en Usado.</p>

            <x-wire-textarea id="certification-reopen-reason" wire:model="form.reason" label="Motivo" rows="3" placeholder="Por ejemplo: foto del sello borrosa" maxlength="{{ \App\Livewire\Forms\CertificationReopeningForm::REASON_MAX }}" required />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="certification-reopen-form" wire:loading.attr="disabled" wire:target="reopen">Reaperturar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
