@props(['locked' => false])

<flux:checkbox
    wire:model="form.withoutSeries"
    :disabled="$locked"
    label="No utiliza serie"
    description="Al ingresar un lote no se piden el inicio ni el final de serie: cada dispositivo es su correlativo con ceros."
/>
<flux:checkbox
    wire:model="form.requiresLotApproval"
    :disabled="$locked"
    label="Requiere aprobación en lote"
    description="Sus dispositivos nacen Inactivos hasta que se registra la aprobación de la naviera. Si se desmarca, nacen Disponibles."
/>

@if ($locked)
    <p role="status" class="m-0 flex gap-2.5 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
        <x-feather name="alert-triangle" class="mt-0.5 size-4.5 flex-none" />
        <span>Este tipo ya tiene lotes, así que «No utiliza serie» y «Requiere aprobación en lote» ya no se pueden cambiar. Si se creó mal, crea otro tipo.</span>
    </p>
@endif
