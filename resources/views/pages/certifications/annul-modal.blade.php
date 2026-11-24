<div x-data="photoField('form.photo', 'form.thumbnail', () => $wire.form.pouchCode + ' · ' + @js(auth()->user()?->name ?? ''))">
    <x-wire-modal-card :title="'Anular la asignación de '.$form->container" wire:model="form.openModal">
        <form id="certification-annul-form" wire:submit="annul" class="grid gap-4">
            <p class="m-0 text-base text-ink-muted">Úsalo cuando se cancela el servicio de despacho. La certificación queda Anulada: se conserva, pero ya no se edita ni se cierra. <strong>No se deshace.</strong></p>

            <x-wire-textarea id="certification-annul-reason" wire:model="form.reason" label="Motivo" rows="3" placeholder="Por ejemplo: la naviera canceló el despacho" maxlength="{{ \App\Livewire\Forms\CertificationAnnulmentForm::REASON_MAX }}" required />

            <fieldset class="m-0 grid gap-2 border-0 p-0">
                <legend class="mb-1.75 text-sm font-medium text-ink">¿Qué se hace con el kit {{ $form->pouchCode }}? Según responda la naviera.</legend>
                @foreach ($decisions as $decision)
                    <label class="flex cursor-pointer items-start gap-2.5 rounded-control border border-line p-3 has-checked:border-brand has-checked:bg-badge-info">
                        <input type="radio" wire:model="form.kitDecision" value="{{ $decision->value }}" class="mt-1 size-4 accent-brand">
                        <span class="flex flex-col gap-0.5">
                            <strong class="text-sm font-medium">{{ $decision->label() }}</strong>
                            <small class="text-sm text-ink-muted">{{ $decision->description() }}</small>
                        </span>
                    </label>
                @endforeach
                <flux:error name="form.kitDecision" />
            </fieldset>

            <div x-show="$wire.form.kitDecision === @js(\App\Enums\CertificationKitDecision::Disable->value)" x-cloak class="flex flex-col gap-1.5">
                <x-photo-field label="Foto del kit inhabilitado" name="form.photo" :title="'Foto del kit '.$form->pouchCode" />
                <span class="text-sm text-ink-muted">Queda como foto del descarte de cada dispositivo del kit.</span>
            </div>
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="danger" type="submit" form="certification-annul-form" wire:loading.attr="disabled" wire:target="annul" x-bind:disabled="uploading">Anular asignación</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
