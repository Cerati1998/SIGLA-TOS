<?php

namespace App\Livewire\Tables;

use App\Enums\SecurityDeviceStatus;
use App\Models\SecurityDevice;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class SecurityDevicesTable extends BaseTable
{
    /**
     * The lot whose devices are listed.
     */
    #[Locked]
    public int $lotId;

    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects(['security_devices.id as id'])
            ->setSearchPlaceholder('Serie')
            ->setEmptyMessage('No se encontraron dispositivos.')
            ->setConfigurableAreas([
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the devices of the lot, in the order of their serial numbers.
     *
     * @return Builder<SecurityDevice>
     */
    public function builder(): Builder
    {
        return SecurityDevice::query()
            ->where('security_device_lot_id', $this->lotId)
            ->orderBy('security_devices.id');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Serie', 'serial_number')->searchable(),
            Column::make('Estado', 'status')
                ->format(fn (SecurityDeviceStatus $value): View => view('pages.security-device-lots.partials.status', ['status' => $value])),
            Column::make('Kit')
                ->label(fn (): string => '—'),
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
                ->options(['' => 'Todos los estados'] + SecurityDeviceStatus::options())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('security_devices.status', $value)),
        ];
    }
}
