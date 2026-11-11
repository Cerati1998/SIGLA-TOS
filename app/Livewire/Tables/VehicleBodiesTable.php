<?php

namespace App\Livewire\Tables;

use App\Models\VehicleBody;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class VehicleBodiesTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects(['vehicle_bodies.id as id'])
            ->setSearchPlaceholder('Nombre')
            ->setEmptyMessage('No se encontraron carrocerías.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.vehicle-bodies.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, with the deactivated bodies too, ordered by name and with how many vehicles use them.
     *
     * @return Builder<VehicleBody>
     */
    public function builder(): Builder
    {
        return VehicleBody::withTrashed()->withCount('vehicles')->orderBy('name');
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
            Column::make('Vehículos')
                ->label(fn (VehicleBody $row): string => (string) $row->vehicles_count),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, VehicleBody $row): View => view('pages.vehicle-bodies.partials.status', ['vehicleBody' => $row])),
            Column::make('Acciones')
                ->label(fn (VehicleBody $row): View => view('pages.vehicle-bodies.partials.actions', ['vehicleBody' => $row])),
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
                ->options(['' => 'Todos', 'active' => 'Activa', 'inactive' => 'Desactivada'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $value === 'active'
                    ? $builder->whereNull('vehicle_bodies.deleted_at')
                    : $builder->whereNotNull('vehicle_bodies.deleted_at')),
        ];
    }

    /**
     * Deactivate the given body. The vehicles that have it keep it.
     */
    public function deactivate(int $id): void
    {
        $vehicleBody = VehicleBody::withTrashed()->findOrFail($id);

        if ($vehicleBody->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Carrocería ya desactivada', text: 'Esta carrocería ya está desactivada.');

            return;
        }

        $vehicleBody->delete();

        $this->dispatch('toast', icon: 'success', title: 'Carrocería desactivada', text: 'La carrocería se desactivó correctamente.');
    }

    /**
     * Reactivate the given body.
     */
    public function reactivate(int $id): void
    {
        $vehicleBody = VehicleBody::withTrashed()->findOrFail($id);

        if (! $vehicleBody->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Carrocería ya activa', text: 'Esta carrocería ya está activa.');

            return;
        }

        $vehicleBody->restore();

        $this->dispatch('toast', icon: 'success', title: 'Carrocería reactivada', text: 'La carrocería se reactivó correctamente.');
    }
}
