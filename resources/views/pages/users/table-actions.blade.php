<div x-data="{ id: {{ $user->id }}, name: @js($user->name) }" class="flex items-center gap-1">
    <flux:button variant="ghost" x-on:click="$dispatch('edit-user', { userId: id })">Editar</flux:button>

    @if ($user->trashed())
        @can('restore', $user)
            <flux:button variant="ghost" x-on:click="$wire.reactivate(id)">Reactivar</flux:button>
        @endcan
    @else
        @can('delete', $user)
            <flux:button variant="ghost" x-on:click="confirmAction('¿Desactivar a ' + name + '?', 'Ya no podrá iniciar sesión.', () => $wire.deactivate(id))">Desactivar</flux:button>
        @endcan
    @endif
</div>
