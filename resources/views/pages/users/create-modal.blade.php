<div>
    <x-wire-modal-card
        title="Nuevo usuario"
        name="user-create"
        wire:model="form.openModal"
        x-on:open="$wire.form.name = ''; $wire.form.email = ''"
        x-on:close="$wire.$errors.any() && $wire.clearErrors()"
    >
        <form id="user-create-form" wire:submit="save" class="grid gap-4">
            <x-pages::users.fields id-prefix="user-create" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="user-create-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>

    <x-wire-modal-card title="Usuario creado" persistent wire:model="showPassword">
        @if ($generatedPassword !== null)
            <div class="grid gap-4">
                <p class="m-0">{{ $form->name }} · {{ $form->email }}</p>

                <x-pages::users.generated-password :password="$generatedPassword" />

                <p class="m-0 rounded-control bg-badge-warning p-3.5 text-sm text-badge-warning-ink">
                    Copia esta contraseña y entrégasela al usuario. No se volverá a mostrar.
                </p>
            </div>
        @endif

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="$openModal('user-create')" wire:click="closePassword" wire:loading.attr="disabled" wire:target="closePassword">Crear otro</flux:button>
                <flux:button variant="primary" type="button" wire:click="closePassword" wire:loading.attr="disabled" wire:target="closePassword">Cerrar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
