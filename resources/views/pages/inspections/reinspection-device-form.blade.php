@php
    $device = $reinspectionDevice->device;
    $typeName = $device->lot->securityDeviceType->name;
@endphp

<div x-data="photoField('photo', 'thumbnail', @js('Descarte '.$device->serial_number))"
    class="flex flex-col gap-2.5 rounded-card border-2 border-danger bg-badge-danger p-3">
    <div class="flex items-center justify-between gap-2">
        <s class="font-heading text-xl font-medium text-ink-muted" aria-label="Serie anterior {{ $device->serial_number }}">{{ $device->serial_number }}</s>
        <span class="inline-block rounded-badge bg-danger px-2 py-1 text-xs text-white">Incorrecta en checkpoint</span>
    </div>

    <div class="flex flex-col gap-1.5">
        <label for="reinspection-serial-{{ $reinspectionDevice->id }}" class="text-[15px] font-medium">{{ $typeName }} nueva</label>
        <div class="flex items-center gap-2">
            <div class="min-w-0 flex-1">
                <x-scan-input
                    id="reinspection-serial-{{ $reinspectionDevice->id }}"
                    wire:model="serialNumber"
                    :invalid="$errors->has('serialNumber')"
                    placeholder="Escanea o escribe la serie"
                    x-on:keydown.enter.prevent
                />
            </div>
            <x-camera-scanner title="Escanear la serie nueva" x-on:barcode-detected="$wire.serialNumber = $event.detail.value" />
        </div>
        <flux:error name="serialNumber" />
        <span class="text-[13px] text-ink-muted">
            Debe ser un dispositivo de tipo {{ $typeName }} despachado a granel para este contenedor{{ $dispatchCodes === [] ? '.' : ' ('.implode(', ', $dispatchCodes).').' }}
            @if ($dispatchCodes === [])
                Todavía no hay extras sin usar de este tipo: pídelos a bodega.
            @endif
        </span>
    </div>

    <x-photo-field label="Foto del dispositivo que sale" name="photo" :title="'Descarte de '.$device->serial_number" />
    <span class="text-[13px] text-ink-muted">Obligatoria. Queda como foto del descarte de {{ $device->serial_number }}, como en el reemplazo de dispositivos extra.</span>

    <button type="button" wire:click="replace" wire:loading.attr="disabled" wire:target="replace" x-bind:disabled="uploading"
        class="inline-flex h-13 w-full items-center justify-center gap-2 rounded-control bg-brand text-base font-medium text-white disabled:opacity-50">
        <i class="fa-solid fa-right-left text-sm" aria-hidden="true"></i>
        Reemplazar
    </button>
    <span class="text-[13px] text-ink-muted">Al reemplazar, {{ $device->serial_number }} pasa a Descartado con el motivo de la reinspección y esta foto.</span>
</div>
