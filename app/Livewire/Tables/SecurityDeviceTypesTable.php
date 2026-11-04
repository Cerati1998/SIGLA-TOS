<?php

namespace App\Livewire\Tables;

use App\Models\SecurityDeviceType;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class SecurityDeviceTypesTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects(['security_device_types.id as id'])
            ->setSearchPlaceholder('Nombre')
            ->setEmptyMessage('No se encontraron tipos de dispositivo.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.security-device-types.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, with the deactivated types too, ordered by name.
     *
     * @return Builder<SecurityDeviceType>
     */
    public function builder(): Builder
    {
        return SecurityDeviceType::withTrashed()->orderBy('name');
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
            Column::make('Tipo', 'kind')
                ->format(fn ($value, SecurityDeviceType $row): string => $row->kind->label()),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, SecurityDeviceType $row): View => view('pages.security-device-types.partials.status', ['securityDeviceType' => $row])),
            Column::make('Acciones')
                ->label(fn (SecurityDeviceType $row): View => view('pages.security-device-types.partials.actions', ['securityDeviceType' => $row])),
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
     * Deactivate the given security device type.
     */
    public function deactivate(int $id): void
    {
        $securityDeviceType = $this->builder()->findOrFail($id);

        if ($securityDeviceType->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Tipo de dispositivo ya desactivado', text: 'Este tipo de dispositivo ya está desactivado.');

            return;
        }

        $securityDeviceType->delete();

        $this->dispatch('toast', icon: 'success', title: 'Tipo de dispositivo desactivado', text: 'El tipo de dispositivo se desactivó correctamente.');
    }

    /**
     * Reactivate the given security device type.
     */
    public function reactivate(int $id): void
    {
        $securityDeviceType = $this->builder()->findOrFail($id);

        if (! $securityDeviceType->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Tipo de dispositivo ya activo', text: 'Este tipo de dispositivo ya está activo.');

            return;
        }

        $securityDeviceType->restore();

        $this->dispatch('toast', icon: 'success', title: 'Tipo de dispositivo reactivado', text: 'El tipo de dispositivo se reactivó correctamente.');
    }
}
