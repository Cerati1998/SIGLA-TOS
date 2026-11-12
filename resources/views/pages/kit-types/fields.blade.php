@props([
    'idPrefix',
    'containerTypes',
    'deviceTypes',
])

<div
    x-data="{
        names: @js($deviceTypes->pluck('name', 'id')),
        get selected() {
            return Object.entries($wire.form.devices ?? {})
                .filter(([id, device]) => device.on)
                .map(([id, device]) => ({ id: id, name: this.names[id], quantity: parseInt(device.quantity) || 0 }));
        },
        get total() {
            return this.selected.reduce((sum, device) => sum + device.quantity, 0);
        },
    }"
    class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_17.5rem]"
>
    <div class="flex min-w-0 flex-col gap-6">
        <section class="flex flex-col gap-4" aria-labelledby="{{ $idPrefix }}-step-1">
            <x-pages::kit-types.step number="1" id="{{ $idPrefix }}-step-1" title="Datos del tipo de kit" text="Así lo elegirá bodega al armar un kit." />

            <div class="grid gap-4 md:grid-cols-2 lg:ps-10">
                <x-wire-input id="{{ $idPrefix }}-name" wire:model="form.name" label="Nombre" type="text" maxlength="100" required autocomplete="off" />
                <x-wire-native-select id="{{ $idPrefix }}-container-type" wire:model="form.containerTypeId" label="Tipo de contenedor" :options="['' => 'Elige un tipo de contenedor'] + $containerTypes" option-key-value required />
            </div>
        </section>

        <section class="flex flex-col gap-4" aria-labelledby="{{ $idPrefix }}-step-2">
            <x-pages::kit-types.step number="2" id="{{ $idPrefix }}-step-2" title="¿Qué lleva el kit?" text="Enciende cada dispositivo que lleva el kit e indica cuántos." />

            <div class="flex flex-col gap-2 lg:ps-10">
                <div class="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($deviceTypes as $deviceType)
                        <x-pages::kit-types.device-card :id-prefix="$idPrefix" :device-type="$deviceType" wire:key="{{ $idPrefix }}-device-{{ $deviceType->id }}" />
                    @endforeach
                </div>

                @error('form.devices')
                    <p class="text-sm text-danger">{{ $message }}</p>
                @enderror

                <p class="text-sm text-ink-muted">Solo aparecen los tipos de dispositivo activos.</p>
            </div>
        </section>
    </div>

    <x-pages::kit-types.summary />
</div>
