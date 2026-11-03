<div x-data="{ id: {{ $user->id }}, name: @js($user->name) }" class="flex items-center gap-1 [&_button]:max-md:size-11">
    <x-wire-button positive x-on:click="$dispatch('edit-user', { userId: id })" title="Editar" aria-label="Editar" xs>
        <i class="fa-solid fa-pen text-sm" aria-hidden="true"></i>
    </x-wire-button>

    @if ($user->trashed())
        @can('restore', $user)
            <x-wire-button primary x-on:click="$wire.reactivate(id)" wire:loading.attr="disabled" wire:target="deactivate,reactivate" title="Reactivar" aria-label="Reactivar" xs>
                <i class="fa-solid fa-user-check text-sm" aria-hidden="true"></i>
            </x-wire-button>
        @endcan
    @else
        @can('delete', $user)
            <x-wire-button negative x-on:click="confirmAction('¿Desactivar a ' + name + '?', 'Ya no podrá iniciar sesión.', () => $wire.deactivate(id))" wire:loading.attr="disabled" wire:target="deactivate,reactivate" title="Desactivar" aria-label="Desactivar" xs>
                <i class="fa-solid fa-user-xmark text-sm" aria-hidden="true"></i>
            </x-wire-button>
        @endcan
    @endif
</div>
