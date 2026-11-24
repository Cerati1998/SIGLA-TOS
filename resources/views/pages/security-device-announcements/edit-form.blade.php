<form wire:submit="save" class="flex max-w-190 flex-col gap-5.25">
    <x-panel.form-card>
        <x-slot:heading>Anuncio</x-slot:heading>

        <fieldset wire:loading.attr="disabled" wire:target="save" class="col-span-full grid grid-cols-1 items-start gap-x-6 gap-y-3.5 md:grid-cols-2">
            <flux:select wire:model="form.shippingLineId" label="Línea naviera" placeholder="Elige una línea naviera" required>
                @foreach ($this->shippingLines as $shippingLine)
                    <flux:select.option value="{{ $shippingLine->id }}">{{ $shippingLine->code }} · {{ $shippingLine->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="form.providerId" label="Proveedor" placeholder="Elige un proveedor" required>
                @foreach ($this->providers as $provider)
                    <flux:select.option value="{{ $provider->id }}">{{ $provider->business_name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="form.receivedAt" label="Fecha de recepción" type="date" required />

            <flux:input wire:model="form.dispatchNumber" label="Número de guía" type="text" maxlength="50" required autocomplete="off" />
        </fieldset>

        <p role="status" class="col-span-full m-0 flex gap-2.5 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
            <x-feather name="info" class="mt-0.5 size-4.5 flex-none" />
            <span>Lo que cambies aquí vale para {{ $lotsCount === 1 ? 'el lote' : 'los '.$lotsCount.' lotes' }} del anuncio.</span>
        </p>

        <x-slot:actions>
            <flux:button :href="route('intranet.security-device-announcements.show', $announcement)" wire:navigate>Cancelar</flux:button>
            <flux:button variant="primary" type="submit">Guardar cambios</flux:button>
        </x-slot:actions>
    </x-panel.form-card>
</form>
