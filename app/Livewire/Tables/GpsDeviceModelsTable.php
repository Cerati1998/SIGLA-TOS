<?php

namespace App\Livewire\Tables;

use App\Enums\GpsDeviceCategory;
use App\Models\GpsDeviceModel;
use App\Models\Provider;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class GpsDeviceModelsTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects([
            'gps_device_models.id as id',
            'gps_device_models.brand_id as brand_id',
            'gps_device_models.monitoring_product_code as monitoring_product_code',
            'gps_device_models.monitoring_device_type_code as monitoring_device_type_code',
        ])
            ->setSearchPlaceholder('Nombre o código')
            ->setEmptyMessage('No se encontraron modelos GPS.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.gps-device-models.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, with the deactivated models too, ordered by brand and name.
     *
     * @return Builder<GpsDeviceModel>
     */
    public function builder(): Builder
    {
        return GpsDeviceModel::withTrashed()
            ->with('brand')
            ->orderBy(Provider::withTrashed()->select('business_name')->whereColumn('providers.id', 'gps_device_models.brand_id'))
            ->orderBy('gps_device_models.name');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Marca')
                ->label(fn (GpsDeviceModel $row): string => $row->brand->business_name),
            Column::make('Nombre', 'name')->searchable(),
            Column::make('Categoría', 'category')
                ->format(fn (GpsDeviceCategory $value): string => $value->label()),
            Column::make('Apertura', 'opening_type')
                ->format(fn ($value, GpsDeviceModel $row): string => $row->opening_type->label()),
            Column::make('Código de monitoreo')
                ->label(fn (GpsDeviceModel $row): string => $row->monitoringCode() ?? '—')
                ->searchable(fn (Builder $query, string $search) => $query
                    ->orWhere('gps_device_models.monitoring_product_code', 'like', "%{$search}%")
                    ->orWhere('gps_device_models.monitoring_device_type_code', 'like', "%{$search}%")),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, GpsDeviceModel $row): View => view('pages.gps-device-models.partials.status', ['gpsDeviceModel' => $row])),
            Column::make('Acciones')
                ->label(fn (GpsDeviceModel $row): View => view('pages.gps-device-models.partials.actions', ['gpsDeviceModel' => $row])),
        ];
    }

    /**
     * Get the filters of the table. The brands are the ones that have a model, even if they were deactivated.
     *
     * @return array<int, Filter>
     */
    public function filters(): array
    {
        return [
            SelectFilter::make('Marca', 'brand')
                ->options(['' => 'Todas'] + Provider::withTrashed()
                    ->whereIn('id', GpsDeviceModel::withTrashed()->select('brand_id'))
                    ->orderBy('business_name')
                    ->pluck('business_name', 'id')
                    ->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('gps_device_models.brand_id', (int) $value)),
            SelectFilter::make('Categoría', 'category')
                ->options(['' => 'Todas'] + GpsDeviceCategory::options())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('gps_device_models.category', $value)),
            SelectFilter::make('Estado', 'status')
                ->options(['' => 'Todos', 'active' => 'Activo', 'inactive' => 'Desactivado'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $value === 'active'
                    ? $builder->whereNull('gps_device_models.deleted_at')
                    : $builder->whereNotNull('gps_device_models.deleted_at')),
        ];
    }

    /**
     * Deactivate the given model.
     */
    public function deactivate(int $id): void
    {
        $gpsDeviceModel = $this->builder()->findOrFail($id);

        if ($gpsDeviceModel->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Modelo GPS ya desactivado', text: 'Este modelo GPS ya está desactivado.');

            return;
        }

        $gpsDeviceModel->delete();

        $this->dispatch('toast', icon: 'success', title: 'Modelo GPS desactivado', text: 'El modelo GPS se desactivó correctamente.');
    }

    /**
     * Reactivate the given model.
     */
    public function reactivate(int $id): void
    {
        $gpsDeviceModel = $this->builder()->findOrFail($id);

        if (! $gpsDeviceModel->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Modelo GPS ya activo', text: 'Este modelo GPS ya está activo.');

            return;
        }

        $gpsDeviceModel->restore();

        $this->dispatch('toast', icon: 'success', title: 'Modelo GPS reactivado', text: 'El modelo GPS se reactivó correctamente.');
    }
}
