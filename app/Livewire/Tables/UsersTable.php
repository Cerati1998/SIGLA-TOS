<?php

namespace App\Livewire\Tables;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class UsersTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setDefaultSort('name')
            ->setAdditionalSelects(['users.id as id'])
            ->setSearchPlaceholder('Nombre o correo')
            ->setEmptyMessage('No se encontraron usuarios.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.users.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, with the deactivated users too.
     *
     * @return Builder<User>
     */
    public function builder(): Builder
    {
        return User::withTrashed();
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Nombre', 'name')->searchable(),
            Column::make('Correo electrónico', 'email')->searchable(),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, User $row): View => view('pages.users.partials.status', ['user' => $row])),
            Column::make('Acciones')
                ->label(fn (User $row): View => view('pages.users.partials.actions', ['user' => $row])),
        ];
    }

    /**
     * Get the filters of the table.
     *
     * @return array<int, Filter>
     */
    public function filters(): array
    {
        return [
            SelectFilter::make('Estado', 'status')
                ->options(['' => 'Todos', 'active' => 'Activo', 'inactive' => 'Inactivo'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $value === 'active'
                    ? $builder->whereNull('deleted_at')
                    : $builder->whereNotNull('deleted_at')),
        ];
    }

    /**
     * Deactivate the given user.
     */
    public function deactivate(int $id): void
    {
        $user = $this->builder()->findOrFail($id);

        $this->authorize('delete', $user);

        if ($user->trashed()) {
            $this->dispatch('toast', icon: 'warning', text: 'Este usuario ya está desactivado.');

            return;
        }

        $user->delete();

        $this->dispatch('toast', icon: 'success', text: 'Usuario desactivado.');
    }

    /**
     * Reactivate the given user.
     */
    public function reactivate(int $id): void
    {
        $user = $this->builder()->findOrFail($id);

        $this->authorize('restore', $user);

        if (! $user->trashed()) {
            $this->dispatch('toast', icon: 'warning', text: 'Este usuario ya está activo.');

            return;
        }

        $user->restore();

        $this->dispatch('toast', icon: 'success', text: 'Usuario reactivado.');
    }
}
