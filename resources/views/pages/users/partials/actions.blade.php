<div x-data="{ id: {{ $user->id }}, name: @js($user->name) }" class="flex items-center gap-1 [&_button]:max-md:size-11">
    <x-wire-button positive x-on:click="$dispatch('edit-user', { userId: id })" title="Editar" aria-label="Editar" xs>
        <x-feather name="edit-2" class="size-4 text-ink" />
    </x-wire-button>

    @if ($user->trashed())
        @can('restore', $user)
            <x-wire-button primary x-on:click="$wire.reactivate(id)" wire:loading.attr="disabled" wire:target="deactivate,reactivate" title="Reactivar" aria-label="Reactivar" xs>
                <x-feather name="user-check" class="size-4" />
            </x-wire-button>
        @endcan
    @else
        @can('delete', $user)
            <x-wire-button negative x-on:click="confirmAction('¿Desactivar a ' + name + '?', 'Ya no podrá iniciar sesión.', () => $wire.deactivate(id))" wire:loading.attr="disabled" wire:target="deactivate,reactivate" title="Desactivar" aria-label="Desactivar" xs>
                <x-feather name="user-x" class="size-4" />
            </x-wire-button>
        @endcan
    @endif
</div>
