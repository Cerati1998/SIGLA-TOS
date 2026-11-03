<div>
    <x-wire-modal-card :title="$form->userId === null ? 'Nuevo usuario' : 'Editar usuario'" wire:model="form.openModal">
        <form id="user-form" wire:submit="save" class="grid gap-4">
            <x-pages::users.fields />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" form="user-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
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
                <flux:button type="button" wire:click="create" wire:loading.attr="disabled" wire:target="create">Crear otro</flux:button>
                <flux:button variant="primary" type="button" wire:click="closePassword" wire:loading.attr="disabled" wire:target="closePassword">Cerrar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
