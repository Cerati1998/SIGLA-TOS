{{-- The security devices the section carries: one row per type, with its quantity. Rows are added and removed in the browser. --}}
<div class="flex flex-col gap-2 lg:ps-10">
    <template x-for="(device, index) in rows" x-bind:key="device.id">
        <div
            class="flex flex-wrap items-center gap-3 rounded-card border-2 px-3 py-2.5"
            x-bind:class="find(device.id).trashed ? 'border-dashed border-highlight bg-badge-warning' : 'border-brand bg-badge-info'"
        >
            <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                <span class="text-sm font-semibold text-ink" x-text="find(device.id).name"></span>
                <span
                    class="text-xs"
                    x-bind:class="find(device.id).trashed ? 'text-badge-warning-ink' : 'text-ink-muted'"
                    x-text="find(device.id).trashed ? 'Desactivado: se conserva mientras no lo quites; si lo quitas, ya no podrás volver a elegirlo.' : find(device.id).kind"
                ></span>
            </span>

            <span class="inline-flex items-center overflow-hidden rounded-control border border-control bg-white">
                <button
                    type="button"
                    x-on:click="device.quantity = Math.max(1, (parseInt(device.quantity) || 1) - 1)"
                    x-bind:disabled="(parseInt(device.quantity) || 1) <= 1"
                    x-bind:aria-label="'Quitar uno de ' + find(device.id).name"
                    class="inline-flex size-8 cursor-pointer items-center justify-center text-brand disabled:cursor-not-allowed disabled:text-control max-md:size-11"
                >
                    <i class="fa-solid fa-minus text-xs" aria-hidden="true"></i>
                </button>
                <input
                    type="number"
                    min="1"
                    max="{{ \App\Livewire\Forms\CertificationSectionForm::MAX_QUANTITY }}"
                    step="1"
                    inputmode="numeric"
                    x-model="device.quantity"
                    x-bind:aria-label="'Cantidad de ' + find(device.id).name"
                    class="h-8 w-12 border-x border-line text-center font-heading text-base font-medium text-ink [appearance:textfield] focus:outline-none max-md:h-11 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                >
                <button
                    type="button"
                    x-on:click="device.quantity = Math.min({{ \App\Livewire\Forms\CertificationSectionForm::MAX_QUANTITY }}, (parseInt(device.quantity) || 0) + 1)"
                    x-bind:aria-label="'Agregar uno de ' + find(device.id).name"
                    class="inline-flex size-8 cursor-pointer items-center justify-center text-brand max-md:size-11"
                >
                    <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
                </button>
            </span>

            <x-wire-button negative x-on:click="remove(index)" title="Quitar" x-bind:aria-label="'Quitar ' + find(device.id).name + ' de la sección'" class="max-md:size-11" xs>
                <i class="fa-solid fa-trash text-sm" aria-hidden="true"></i>
            </x-wire-button>
        </div>
    </template>

    <div x-show="rows.length === 0" class="flex items-center gap-3 rounded-card border-2 border-dashed border-control p-4 text-sm text-ink-muted">
        <x-feather name="camera" class="size-5.5" />
        Esta sección no lleva dispositivos: solo pide fotos.
    </div>

    @error('form.devices')
        <p class="text-sm text-danger">{{ $message }}</p>
    @enderror

    @foreach (collect($errors->get('form.devices.*'))->flatten()->unique() as $message)
        <p class="text-sm text-danger">{{ $message }}</p>
    @endforeach

    <div class="flex flex-wrap items-end gap-2 pt-1">
        <div class="flex min-w-0 flex-1 flex-col gap-1.5">
            <label for="{{ $prefix }}-add-device" class="text-sm font-medium text-ink">Agregar un tipo de dispositivo</label>
            <select
                id="{{ $prefix }}-add-device"
                x-model="pick"
                class="h-8.5 w-full rounded-control border border-control bg-white px-2.5 text-base text-ink max-md:h-11"
            >
                <option value="">Elige un tipo de dispositivo</option>
                <template x-for="type in available" x-bind:key="type.id">
                    <option x-bind:value="type.id" x-text="type.name"></option>
                </template>
            </select>
        </div>

        <flux:button type="button" x-on:click="add()" x-bind:disabled="pick === ''" class="max-md:w-full">
            <i class="fa-solid fa-plus text-sm" aria-hidden="true"></i>
            Agregar
        </flux:button>
    </div>

    <p class="text-sm text-ink-muted">Solo tipos activos que la sección todavía no lleva. Entra con cantidad 1.</p>
</div>
