<?php

namespace App\Livewire\Tables;

use App\Enums\VehicleCategory;
use App\Enums\VehicleKind;
use App\Models\Vehicle;
use App\Models\VehicleBody;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class VehiclesTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects(['vehicles.id as id', 'vehicles.kind as kind', 'vehicles.category as category', 'vehicles.vehicle_body_id as vehicle_body_id'])
            ->setSearchPlaceholder('Placa o marca')
            ->setEmptyMessage('No se encontraron vehículos.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.vehicles.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, with the deactivated vehicles too, ordered by plate.
     *
     * @return Builder<Vehicle>
     */
    public function builder(): Builder
    {
        return Vehicle::withTrashed()->with('body')->orderBy('vehicles.plate');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Placa', 'plate')->searchable(),
            Column::make('Tipo')
                ->label(fn (Vehicle $row): View => view('pages.vehicles.partials.kind', ['vehicle' => $row])),
            Column::make('Carrocería')
                ->label(fn (Vehicle $row): string => $row->body->name),
            Column::make('Marca', 'brand')->searchable(),
            Column::make('Categoría')
                ->label(fn (Vehicle $row): string => $row->category->label()),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, Vehicle $row): View => view('pages.vehicles.partials.status', ['vehicle' => $row])),
            Column::make('Acciones')
                ->label(fn (Vehicle $row): View => view('pages.vehicles.partials.actions', ['vehicle' => $row])),
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
            SelectFilter::make('Tipo', 'kind')
                ->options(['' => 'Todos', ...VehicleKind::options()])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('vehicles.kind', $value)),
            SelectFilter::make('Carrocería', 'body')
                ->options(['' => 'Todas'] + VehicleBody::withTrashed()->orderBy('name')->pluck('name', 'id')->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('vehicles.vehicle_body_id', $value)),
            SelectFilter::make('Categoría', 'category')
                ->options(['' => 'Todas', ...VehicleCategory::options()])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('vehicles.category', $value)),
            SelectFilter::make('Estado', 'status')
                ->options(['' => 'Todos', 'active' => 'Activo', 'inactive' => 'Desactivado'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $value === 'active'
                    ? $builder->whereNull('vehicles.deleted_at')
                    : $builder->whereNotNull('vehicles.deleted_at')),
        ];
    }

    /**
     * Deactivate the given vehicle.
     */
    public function deactivate(int $id): void
    {
        $vehicle = Vehicle::withTrashed()->findOrFail($id);

        if ($vehicle->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Vehículo ya desactivado', text: 'Este vehículo ya está desactivado.');

            return;
        }

        $vehicle->delete();

        $this->dispatch('toast', icon: 'success', title: 'Vehículo desactivado', text: 'El vehículo se desactivó correctamente.');
    }

    /**
     * Reactivate the given vehicle.
     */
    public function reactivate(int $id): void
    {
        $vehicle = Vehicle::withTrashed()->findOrFail($id);

        if (! $vehicle->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Vehículo ya activo', text: 'Este vehículo ya está activo.');

            return;
        }

        $vehicle->restore();

        $this->dispatch('toast', icon: 'success', title: 'Vehículo reactivado', text: 'El vehículo se reactivó correctamente.');
    }
}
