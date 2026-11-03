<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Usuarios')] class extends Component {
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    /**
     * Pass the breadcrumbs to the layout.
     */
    public function rendering(View $view): void
    {
        $view->layoutData(['breadcrumbs' => [
            ['name' => 'Inicio', 'route' => route('intranet.dashboard')],
            ['name' => 'Usuarios'],
        ]]);
    }

    /**
     * Go back to the first page when the search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Go back to the first page when the status filter changes.
     */
    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Get the users that match the search and the status filter.
     *
     * @return LengthAwarePaginator<int, User>
     */
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::withTrashed()
            ->when($this->status === 'active', fn (Builder $query) => $query->whereNull('deleted_at'))
            ->when($this->status === 'inactive', fn (Builder $query) => $query->whereNotNull('deleted_at'))
            ->when($this->search !== '', fn (Builder $query) => $query->where(
                fn (Builder $query) => $query
                    ->whereLike('name', "%{$this->search}%")
                    ->orWhereLike('email', "%{$this->search}%"),
            ))
            ->orderBy('name')
            ->paginate(10);
    }

    /**
     * Deactivate the given user.
     */
    public function deactivate(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        $this->authorize('delete', $user);

        $user->delete();

        Flux::toast(variant: 'success', text: 'Usuario desactivado.');
    }

    /**
     * Reactivate the given user.
     */
    public function reactivate(int $userId): void
    {
        $user = User::onlyTrashed()->findOrFail($userId);

        $this->authorize('restore', $user);

        $user->restore();

        Flux::toast(variant: 'success', text: 'Usuario reactivado.');
    }
}; ?>

<x-panel.list-card>
    <x-slot:filters>
        <flux:field class="min-w-60 flex-1">
            <flux:label>Buscar</flux:label>
            <flux:input wire:model.live.debounce.300ms="search" type="search" placeholder="Nombre o correo" />
        </flux:field>

        <flux:field class="w-full sm:w-50">
            <flux:label>Estado</flux:label>
            <flux:select wire:model.live="status">
                <flux:select.option value="">Todos</flux:select.option>
                <flux:select.option value="active">Activo</flux:select.option>
                <flux:select.option value="inactive">Inactivo</flux:select.option>
            </flux:select>
        </flux:field>

        <flux:button variant="primary" :href="route('intranet.users.create')" wire:navigate>Nuevo</flux:button>
    </x-slot:filters>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Nombre</flux:table.column>
            <flux:table.column>Correo electrónico</flux:table.column>
            <flux:table.column>Estado</flux:table.column>
            <flux:table.column>Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->users as $user)
                <flux:table.row :key="$user->id">
                    <flux:table.cell>{{ $user->name }}</flux:table.cell>
                    <flux:table.cell>{{ $user->email }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($user->trashed())
                            <x-status-badge variant="warning">Inactivo</x-status-badge>
                        @else
                            <x-status-badge variant="success">Activo</x-status-badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            <flux:button variant="ghost" :href="route('intranet.users.edit', $user)" wire:navigate>Editar</flux:button>

                            @if ($user->trashed())
                                @can('restore', $user)
                                    <flux:button variant="ghost" wire:click="reactivate({{ $user->id }})">Reactivar</flux:button>
                                @endcan
                            @else
                                @can('delete', $user)
                                    <flux:button variant="ghost" wire:click="deactivate({{ $user->id }})" wire:confirm="¿Desactivar a {{ $user->name }}? Ya no podrá iniciar sesión.">Desactivar</flux:button>
                                @endcan
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center text-ink-muted">No se encontraron usuarios.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <x-slot:footer>
        <x-panel.pagination :paginator="$this->users" />
    </x-slot:footer>
</x-panel.list-card>
