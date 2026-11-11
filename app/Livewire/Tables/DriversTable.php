<?php

namespace App\Livewire\Tables;

use App\Models\Driver;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class DriversTable extends BaseTable
{
    /**
     * Configure the table. The search stays out of the URL, because it can hold a DNI or a license.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects(['drivers.id as id'])
            ->setQueryStringForSearchDisabled()
            ->setSearchPlaceholder('DNI, brevete o nombre')
            ->setEmptyMessage('No se encontraron conductores.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.drivers.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, with the deactivated drivers too, ordered by full name.
     *
     * @return Builder<Driver>
     */
    public function builder(): Builder
    {
        return Driver::withTrashed()->orderBy('full_name');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('DNI', 'document_number')->searchable(),
            Column::make('Nombres y apellidos', 'full_name')->searchable(),
            Column::make('Brevete', 'license')->searchable(),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, Driver $row): View => view('pages.drivers.partials.status', ['driver' => $row])),
            Column::make('Acciones')
                ->label(fn (Driver $row): View => view('pages.drivers.partials.actions', ['driver' => $row])),
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
                ->options(['' => 'Todos', 'active' => 'Activo', 'inactive' => 'Desactivado'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $value === 'active'
                    ? $builder->whereNull('deleted_at')
                    : $builder->whereNotNull('deleted_at')),
        ];
    }

    /**
     * Deactivate the given driver.
     */
    public function deactivate(int $id): void
    {
        $driver = $this->builder()->findOrFail($id);

        if ($driver->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Conductor ya desactivado', text: 'Este conductor ya está desactivado.');

            return;
        }

        $driver->delete();

        $this->dispatch('toast', icon: 'success', title: 'Conductor desactivado', text: 'El conductor se desactivó correctamente.');
    }

    /**
     * Reactivate the given driver.
     */
    public function reactivate(int $id): void
    {
        $driver = $this->builder()->findOrFail($id);

        if (! $driver->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Conductor ya activo', text: 'Este conductor ya está activo.');

            return;
        }

        $driver->restore();

        $this->dispatch('toast', icon: 'success', title: 'Conductor reactivado', text: 'El conductor se reactivó correctamente.');
    }
}
