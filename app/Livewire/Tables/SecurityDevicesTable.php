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

        $this->setAdditionalSelects([
            'security_devices.id as id',
            'security_devices.security_device_lot_approval_id as security_device_lot_approval_id',
        ])
            ->setSearchPlaceholder('Serie')
            ->setEmptyMessage('No se encontraron dispositivos.')
            ->setConfigurableAreas([
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the devices of the lot, in the order of their serial numbers, with the approval that made them available.
     *
     * @return Builder<SecurityDevice>
     */
    public function builder(): Builder
    {
        return SecurityDevice::query()
            ->with(['approval', 'kits'])
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
            Column::make('Aprobado')
                ->label(fn (SecurityDevice $row): string => $row->approval?->created_at?->timezone('America/Lima')->format('d/m/Y H:i') ?? '—'),
            Column::make('Kit')
                ->label(fn (SecurityDevice $row): string => $row->kits->first()->pouch_code ?? '—'),
            Column::make('Acciones')
                ->label(fn (SecurityDevice $row): View => view('pages.security-devices.partials.actions', ['device' => $row])),
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
