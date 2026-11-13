<?php

namespace App\Livewire\Tables;

use App\Models\Branch;
use App\Models\YardZone;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class YardZonesTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects(['yard_zones.id as id', 'yard_zones.branch_id as branch_id'])
            ->setSearchPlaceholder('Nombre de la zona')
            ->setEmptyMessage('No se encontraron zonas de patio.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.yard-zones.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, with the deactivated zones too, ordered by branch and name.
     *
     * @return Builder<YardZone>
     */
    public function builder(): Builder
    {
        return YardZone::withTrashed()
            ->with('branch')
            ->orderBy(Branch::withTrashed()->select('name')->whereColumn('branches.id', 'yard_zones.branch_id'))
            ->orderBy('yard_zones.name');
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
            Column::make('Sucursal')
                ->label(fn (YardZone $row): string => $row->branch->name),
            Column::make('Capacidad', 'capacity'),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, YardZone $row): View => view('pages.yard-zones.partials.status', ['yardZone' => $row])),
            Column::make('Acciones')
                ->label(fn (YardZone $row): View => view('pages.yard-zones.partials.actions', ['yardZone' => $row])),
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
            SelectFilter::make('Sucursal', 'branch')
                ->options(['' => 'Todas'] + Branch::withTrashed()->orderBy('name')->pluck('name', 'id')->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('yard_zones.branch_id', $value)),
            SelectFilter::make('Estado', 'status')
                ->options(['' => 'Todos', 'active' => 'Activa', 'inactive' => 'Desactivada'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $value === 'active'
                    ? $builder->whereNull('yard_zones.deleted_at')
                    : $builder->whereNotNull('yard_zones.deleted_at')),
        ];
    }

    /**
     * Deactivate the given zone.
     */
    public function deactivate(int $id): void
    {
        $yardZone = YardZone::withTrashed()->findOrFail($id);

        if ($yardZone->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Zona de patio ya desactivada', text: 'Esta zona de patio ya está desactivada.');

            return;
        }

        $yardZone->delete();

        $this->dispatch('toast', icon: 'success', title: 'Zona de patio desactivada', text: 'La zona de patio se desactivó correctamente.');
    }

    /**
     * Reactivate the given zone.
     */
    public function reactivate(int $id): void
    {
        $yardZone = YardZone::withTrashed()->findOrFail($id);

        if (! $yardZone->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Zona de patio ya activa', text: 'Esta zona de patio ya está activa.');

            return;
        }

        $yardZone->restore();

        $this->dispatch('toast', icon: 'success', title: 'Zona de patio reactivada', text: 'La zona de patio se reactivó correctamente.');
    }
}
