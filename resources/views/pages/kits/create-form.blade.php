<form wire:submit="save" x-data x-init="$nextTick(() => $refs.pouchCode.focus())">
    <x-panel.form-card>
        <div class="col-span-full">
            <x-panel.form-step number="1" title="Datos del kit" text="La funda, el tipo de kit y la naviera son obligatorios y ya no cambian después de crear el kit." />
        </div>

        <fieldset wire:loading.attr="disabled" wire:target="save" class="col-span-full grid grid-cols-1 items-start gap-x-6 gap-y-3.5 md:grid-cols-2">
            <div class="flex flex-col gap-1.75 md:col-span-2">
                <flux:label for="pouch-code">Funda</flux:label>
                <x-scan-input
                    id="pouch-code"
                    x-ref="pouchCode"
                    wire:model="form.pouchCode"
                    :invalid="$errors->has('form.pouchCode')"
                    placeholder="Escanea o escribe"
                    required
                    x-on:keydown.enter.prevent="$el.closest('form').querySelector('select')?.focus()"
                />
                <span class="text-sm text-ink-muted">Escanea una funda Disponible de la naviera del kit. Entra sola en el kit; al escanearla pasa al tipo de kit.</span>
                <flux:error name="form.pouchCode" />
            </div>

            <flux:select wire:model="form.kitTypeId" label="Tipo de kit" placeholder="Elige un tipo de kit" description:trailing="Solo tipos de kit activos." required>
                @foreach ($this->kitTypes as $kitType)
                    <flux:select.option value="{{ $kitType->id }}">{{ $kitType->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="form.shippingLineId" label="Línea naviera" placeholder="Elige una línea naviera" description:trailing="Solo acepta dispositivos de lotes de esta naviera." required>
                @foreach ($this->shippingLines as $shippingLine)
                    <flux:select.option value="{{ $shippingLine->id }}">{{ $shippingLine->code }} · {{ $shippingLine->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </fieldset>

        <x-slot:actions>
            <flux:button :href="route('intranet.kits.index')" wire:navigate class="max-md:h-11">Cancelar</flux:button>
            <flux:button variant="primary" type="submit" :loading="false" class="max-md:h-11">
                <span wire:loading.remove wire:target="save">Crear kit</span>
                <span wire:loading.flex wire:target="save" class="items-center gap-2">
                    <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                    Creando…
                </span>
            </flux:button>
        </x-slot:actions>
    </x-panel.form-card>
</form>
