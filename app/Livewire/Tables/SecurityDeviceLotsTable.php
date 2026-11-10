<?php

namespace App\Livewire\Tables;

use App\Models\Provider;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class SecurityDeviceLotsTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects([
            'security_device_lots.id as id',
            'security_device_lots.security_device_announcement_id as security_device_announcement_id',
        ])
            ->setSearchDisabled()
            ->setEmptyMessage('No se encontraron lotes.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.security-device-lots.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, the latest receptions first.
     *
     * @return Builder<SecurityDeviceLot>
     */
    public function builder(): Builder
    {
        return SecurityDeviceLot::query()
            ->with('announcement')
            ->orderByDesc('security_device_lots.received_at')
            ->orderByDesc('security_device_lots.id');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Tipo', 'announcement.securityDeviceType.name'),
            Column::make('Naviera', 'announcement.shippingLine.code'),
            Column::make('Proveedor', 'announcement.provider.business_name'),
            Column::make('Serie', 'announcement.series_prefix'),
            Column::make('Rango')
                ->label(fn (SecurityDeviceLot $row): string => $row->announcement->rangeLabel()),
            Column::make('Cantidad', 'quantity')
                ->format(fn (int $value): string => number_format($value, 0, ',', ' ')),
            Column::make('Recepción', 'received_at')
                ->format(fn ($value): string => $value->format('d/m/Y')),
            Column::make('Acciones')
                ->label(fn (SecurityDeviceLot $row): View => view('pages.security-device-lots.partials.actions', ['lot' => $row])),
        ];
    }

    /**
     * Get the filters of the table: shipping line, provider, device type and reception dates.
     *
     * @return array<int, Filter>
     */
    public function filters(): array
    {
        return [
            $this->announcementFilter('Naviera', 'shipping_line', 'shipping_line_id', ['' => 'Todas'] + ShippingLine::withTrashed()->orderBy('code')->pluck('code', 'id')->all()),
            $this->announcementFilter('Proveedor', 'provider', 'provider_id', ['' => 'Todos'] + Provider::withTrashed()->orderBy('business_name')->pluck('business_name', 'id')->all()),
            $this->announcementFilter('Tipo de dispositivo', 'security_device_type', 'security_device_type_id', ['' => 'Todos'] + SecurityDeviceType::withTrashed()->orderBy('name')->pluck('name', 'id')->all()),
            DateFilter::make('Recepción desde', 'received_from')
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->whereDate('security_device_lots.received_at', '>=', $value)),
            DateFilter::make('Recepción hasta', 'received_until')
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->whereDate('security_device_lots.received_at', '<=', $value)),
        ];
    }

    /**
     * Build a select filter on a column of the announcement of the lot.
     *
     * @param  array<int|string, string>  $options
     */
    private function announcementFilter(string $name, string $key, string $column, array $options): Filter
    {
        return SelectFilter::make($name, $key)
            ->options($options)
            ->setInputAttributes(self::CONTROL_ATTRIBUTES)
            ->filter(fn (Builder $builder, string $value) => $builder->whereHas(
                'announcement',
                fn (Builder $announcement) => $announcement->where($column, $value),
            ));
    }
}
