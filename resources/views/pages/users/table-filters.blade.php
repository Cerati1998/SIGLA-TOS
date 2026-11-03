<div class="flex flex-wrap items-end gap-3 border-b border-line p-5.25">
    <flux:field class="min-w-60 flex-1">
        <flux:label>Buscar</flux:label>
        <flux:input wire:model.live.debounce.300ms="search" type="search" placeholder="Nombre o correo" />
    </flux:field>

    <flux:field class="w-full sm:w-50">
        <flux:label>Estado</flux:label>
        <flux:select wire:model.live="filterComponents.status">
            <flux:select.option value="">Todos</flux:select.option>
            <flux:select.option value="active">Activo</flux:select.option>
            <flux:select.option value="inactive">Inactivo</flux:select.option>
        </flux:select>
    </flux:field>

    <flux:button variant="primary" x-on:click="$dispatch('create-user')">Nuevo</flux:button>
</div>
