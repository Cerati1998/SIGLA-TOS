<?php

namespace App\Livewire\Tables;

use App\Models\CarrierDriver;
use App\Models\Driver;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

/**
 * The drivers of one carrier, with their state in that carrier.
 */
class CarrierDriversTable extends BaseTable
{
    #[Locked]
    public int $carrierId;

    /**
     * Configure the table. The search stays out of the URL, because it can hold a DNI or a license.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setTableName('carrier-drivers');

        $this->setAdditionalSelects(['drivers.id as id', 'carrier_driver.id as link_id', 'carrier_driver.deleted_at as link_deleted_at'])
            ->setQueryStringStatus(false)
            ->setSearchPlaceholder('DNI, brevete o nombre')
            ->setEmptyMessage('Este transportista todavía no tiene conductores.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.carriers.partials.drivers-options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the drivers linked to the carrier, the deactivated links too, ordered by full name.
     *
     * @return Builder<Driver>
     */
    public function builder(): Builder
    {
        return Driver::withTrashed()
            ->join('carrier_driver', 'carrier_driver.driver_id', '=', 'drivers.id')
            ->where('carrier_driver.carrier_id', $this->carrierId)
            ->orderBy('drivers.full_name');
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
            Column::make('Estado')
                ->label(fn (Driver $row): View => view('pages.carriers.partials.driver-status', ['driver' => $row])),
            Column::make('Acciones')
                ->label(fn (Driver $row): View => view('pages.carriers.partials.driver-actions', ['driver' => $row])),
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
                    ? $builder->whereNull('carrier_driver.deleted_at')
                    : $builder->whereNotNull('carrier_driver.deleted_at')),
        ];
    }

    /**
     * Deactivate the driver in this carrier only.
     */
    public function deactivate(int $linkId): void
    {
        $link = $this->link($linkId);

        if ($link->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Conductor ya desactivado', text: 'Este conductor ya está desactivado en este transportista.');

            return;
        }

        $link->delete();

        $this->dispatch('toast', icon: 'success', title: 'Conductor desactivado', text: 'El conductor se desactivó en este transportista.');
    }

    /**
     * Reactivate the driver in this carrier.
     */
    public function reactivate(int $linkId): void
    {
        $link = $this->link($linkId);

        if (! $link->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Conductor ya activo', text: 'Este conductor ya está activo en este transportista.');

            return;
        }

        $link->restore();

        $this->dispatch('toast', icon: 'success', title: 'Conductor reactivado', text: 'El conductor se reactivó en este transportista.');
    }

    /**
     * Get the given link of this carrier.
     */
    private function link(int $linkId): CarrierDriver
    {
        return CarrierDriver::withTrashed()->where('carrier_id', $this->carrierId)->findOrFail($linkId);
    }
}
