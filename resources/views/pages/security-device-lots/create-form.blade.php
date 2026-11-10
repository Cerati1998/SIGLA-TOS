<form wire:submit="save" class="flex max-w-190 flex-col gap-5.25">
    <x-panel.form-card>
        <x-slot:heading>Origen</x-slot:heading>

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

            <flux:select wire:model="form.securityDeviceTypeId" label="Tipo de dispositivo" placeholder="Elige un tipo" description:trailing="Solo tipos activos." required>
                @foreach ($this->securityDeviceTypes as $securityDeviceType)
                    <flux:select.option value="{{ $securityDeviceType->id }}">{{ $securityDeviceType->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="form.receivedAt" label="Fecha de recepción" type="date" required />

            <flux:input wire:model="form.dispatchNumber" label="Número de guía" type="text" maxlength="50" required autocomplete="off" />
        </fieldset>

        <h3 class="col-span-full m-0 border-t border-line pt-5.25 font-heading text-h3 font-normal">Series</h3>

        <div
            x-data="securityDeviceLotPreview({{ \App\Actions\SecurityDevices\ReceiveSecurityDeviceLot::MAX_DEVICES }})"
            class="col-span-full flex flex-col gap-3.5"
        >
            <fieldset wire:loading.attr="disabled" wire:target="save" class="grid grid-cols-2 items-start gap-x-6 gap-y-3.5 md:grid-cols-[minmax(0,1.2fr)_repeat(3,minmax(0,1fr))]">
                <flux:input wire:model="form.seriesPrefix" label="Serie" type="text" maxlength="20" required autocomplete="off" class:input="uppercase" />
                <flux:input wire:model="form.minDigits" label="Mínimo de dígitos" type="text" inputmode="numeric" maxlength="2" required autocomplete="off" />
                <flux:input wire:model="form.seriesStart" label="Correlativo inicial" type="text" inputmode="numeric" maxlength="15" required autocomplete="off" />
                <flux:input
                    wire:model="form.seriesEnd"
                    label="Correlativo final"
                    type="text"
                    inputmode="numeric"
                    maxlength="15"
                    required
                    autocomplete="off"
                    x-bind:data-invalid="preview.error !== null"
                    x-bind:aria-invalid="preview.error !== null"
                />
            </fieldset>

            <p x-show="preview.error === null && preview.quantity === null" class="m-0 text-sm text-ink-muted">
                Completa la serie y el rango para ver la vista previa.
            </p>

            <p x-show="preview.error !== null" x-cloak x-text="preview.error" role="alert" class="m-0 text-sm text-danger"></p>

            <p x-show="preview.quantity !== null" x-cloak role="status" class="m-0 flex gap-2.5 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
                <x-feather name="info" class="mt-0.5 size-4.5 flex-none" />
                <span>
                    <strong x-text="preview.quantity + (preview.quantity === '1' ? ' dispositivo' : ' dispositivos')"></strong>
                    · primera serie <strong x-text="preview.first"></strong>
                    · última serie <strong x-text="preview.last"></strong>
                </span>
            </p>
        </div>

        @error('form.serialNumbers')
            <p role="alert" class="col-span-full m-0 flex gap-2.5 rounded-control bg-badge-danger p-3.5 text-sm text-badge-danger-ink">
                <x-feather name="alert-circle" class="mt-0.5 size-4.5 flex-none" />
                <span><strong>No se guardó el lote.</strong> {{ $message }}</span>
            </p>
        @enderror

        <fieldset wire:loading.attr="disabled" wire:target="save" class="col-span-full">
            <flux:textarea wire:model="form.notes" label="Observaciones (opcional)" rows="3" />
        </fieldset>

        <x-slot:actions>
            <flux:button :href="route('intranet.dashboard')" wire:navigate>Cancelar</flux:button>
            <flux:button variant="primary" type="submit" :loading="false">
                <span wire:loading.remove wire:target="save">Guardar lote</span>
                <span wire:loading.flex wire:target="save" class="items-center gap-2">
                    <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                    Guardando…
                </span>
            </flux:button>
        </x-slot:actions>
    </x-panel.form-card>
</form>
