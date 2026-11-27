<?php

namespace App\Livewire\Tables;

use App\Enums\GpsDeviceOwnership;
use App\Models\Branch;
use App\Models\GpsDevice;
use App\Models\GpsDeviceModel;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class GpsDevicesTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects([
            'gps_devices.id as id',
            'gps_devices.gps_device_model_id as gps_device_model_id',
            'gps_devices.ownership as ownership',
            'gps_devices.rental_provider_id as rental_provider_id',
            'gps_devices.branch_id as branch_id',
            'gps_devices.status as status',
            'gps_devices.photo_path as photo_path',
            'gps_devices.thumbnail_path as thumbnail_path',
        ])
            ->setSearchPlaceholder('Código o ID de 24HM')
            ->setEmptyMessage('No se encontraron dispositivos GPS.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.gps-devices.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, with the deactivated devices too, ordered by code.
     *
     * @return Builder<GpsDevice>
     */
    public function builder(): Builder
    {
        return GpsDevice::withTrashed()
            ->with(['gpsDeviceModel.brand', 'rentalProvider', 'branch'])
            ->orderBy('gps_devices.code');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Foto')
                ->label(fn (GpsDevice $row): View => view('pages.gps-devices.partials.photo', ['gpsDevice' => $row])),
            Column::make('Código', 'code')->searchable(),
            Column::make('Modelo')
                ->label(fn (GpsDevice $row): View => view('pages.gps-devices.partials.model', ['gpsDevice' => $row])),
            Column::make('Propiedad')
                ->label(fn (GpsDevice $row): View => view('pages.gps-devices.partials.ownership', ['gpsDevice' => $row])),
            Column::make('Sucursal')
                ->label(fn (GpsDevice $row): string => $row->branch->name),
            Column::make('ID de 24HM', 'monitoring_asset_id')
                ->format(fn (?int $value): string => $value === null ? '—' : (string) $value)
                ->searchable(fn (Builder $query, string $search) => $query->orWhere('gps_devices.monitoring_asset_id', 'like', "%{$search}%")),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, GpsDevice $row): View => view('pages.gps-devices.partials.status', ['gpsDevice' => $row])),
            Column::make('Acciones')
                ->label(fn (GpsDevice $row): View => view('pages.gps-devices.partials.actions', ['gpsDevice' => $row])),
        ];
    }

    /**
     * Get the filters of the table. The models and branches are the ones that have a device, even if they were
     * deactivated.
     *
     * @return array<int, Filter>
     */
    public function filters(): array
    {
        return [
            SelectFilter::make('Modelo', 'model')
                ->options(['' => 'Todos'] + GpsDeviceModel::withTrashed()
                    ->whereIn('id', GpsDevice::withTrashed()->select('gps_device_model_id'))
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('gps_devices.gps_device_model_id', (int) $value)),
            SelectFilter::make('Propiedad', 'ownership')
                ->options(['' => 'Todas'] + GpsDeviceOwnership::options())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('gps_devices.ownership', $value)),
            SelectFilter::make('Sucursal', 'branch')
                ->options(['' => 'Todas'] + Branch::withTrashed()
                    ->whereIn('id', GpsDevice::withTrashed()->select('branch_id'))
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('gps_devices.branch_id', (int) $value)),
            SelectFilter::make('Estado', 'status')
                ->options(['' => 'Todos', 'available' => 'Disponible', 'inactive' => 'Desactivado'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $value === 'inactive'
                    ? $builder->whereNotNull('gps_devices.deleted_at')
                    : $builder->whereNull('gps_devices.deleted_at')->where('gps_devices.status', $value)),
        ];
    }

    /**
     * Deactivate the given device: it is discharged.
     */
    public function deactivate(int $id): void
    {
        $gpsDevice = $this->builder()->findOrFail($id);

        if ($gpsDevice->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Dispositivo GPS ya desactivado', text: 'Este dispositivo GPS ya está desactivado.');

            return;
        }

        $gpsDevice->delete();

        $this->dispatch('toast', icon: 'success', title: 'Dispositivo GPS desactivado', text: 'El dispositivo GPS se desactivó correctamente.');
    }

    /**
     * Reactivate the given device.
     */
    public function reactivate(int $id): void
    {
        $gpsDevice = $this->builder()->findOrFail($id);

        if (! $gpsDevice->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Dispositivo GPS ya activo', text: 'Este dispositivo GPS ya está activo.');

            return;
        }

        $gpsDevice->restore();

        $this->dispatch('toast', icon: 'success', title: 'Dispositivo GPS reactivado', text: 'El dispositivo GPS se reactivó correctamente.');
    }
}
