<?php

namespace App\Livewire\Tables;

use App\Actions\Branches\DeactivateBranch;
use App\Models\Branch;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class BranchesTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects(['branches.id as id'])
            ->setSearchPlaceholder('Código o nombre')
            ->setEmptyMessage('No se encontraron sucursales.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.branches.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, with the deactivated branches too, ordered by code.
     *
     * @return Builder<Branch>
     */
    public function builder(): Builder
    {
        return Branch::withTrashed()->orderBy('code');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Código', 'code')->searchable(),
            Column::make('Nombre', 'name')->searchable(),
            Column::make('Dirección', 'address'),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, Branch $row): View => view('pages.branches.partials.status', ['branch' => $row])),
            Column::make('Acciones')
                ->label(fn (Branch $row): View => view('pages.branches.partials.actions', ['branch' => $row])),
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
                ->options(['' => 'Todas', 'active' => 'Activa', 'inactive' => 'Inactiva'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $value === 'active'
                    ? $builder->whereNull('deleted_at')
                    : $builder->whereNotNull('deleted_at')),
        ];
    }

    /**
     * Deactivate the given branch.
     */
    public function deactivate(int $id): void
    {
        $branch = $this->builder()->findOrFail($id);

        if ($branch->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Sucursal ya desactivada', text: 'Esta sucursal ya está desactivada.');

            return;
        }

        $reason = (new DeactivateBranch)->handle($branch);

        if ($reason !== null) {
            $this->dispatch('toast', icon: 'warning', title: 'No se puede desactivar', text: $reason);

            return;
        }

        $this->dispatch('toast', icon: 'success', title: 'Sucursal desactivada', text: 'La sucursal se desactivó correctamente.');
    }

    /**
     * Reactivate the given branch.
     */
    public function reactivate(int $id): void
    {
        $branch = $this->builder()->findOrFail($id);

        if (! $branch->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Sucursal ya activa', text: 'Esta sucursal ya está activa.');

            return;
        }

        $branch->restore();

        $this->dispatch('toast', icon: 'success', title: 'Sucursal reactivada', text: 'La sucursal se reactivó correctamente.');
    }
}
