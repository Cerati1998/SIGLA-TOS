<?php

namespace App\Livewire\Tables;

use App\Enums\SecurityDeviceStatus;
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
     * Get the query of the table, the latest receptions first, with how many devices of each lot were approved (H29).
     *
     * @return Builder<SecurityDeviceLot>
     */
    public function builder(): Builder
    {
        return SecurityDeviceLot::query()
            ->with('announcement.securityDeviceType')
            ->withCount(['devices as approved_devices_count' => fn (Builder $devices) => $devices->where('status', '!=', SecurityDeviceStatus::Inactive->value)])
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
            Column::make('Serie', 'announcement.series_prefix')
                ->format(fn (string $value): string => $value === '' ? '—' : $value),
            Column::make('Rango')
                ->label(fn (SecurityDeviceLot $row): string => $row->announcement->rangeLabel()),
            Column::make('Cantidad', 'quantity')
                ->format(fn (int $value): string => number_format($value, 0, ',', ' ')),
            Column::make('Aprobados')
                ->label(fn (SecurityDeviceLot $row): string => $row->requiresApproval()
                    ? sprintf(
                        '%s de %s',
                        number_format((int) $row->getAttribute('approved_devices_count'), 0, ',', ' '),
                        number_format($row->quantity, 0, ',', ' '),
                    )
                    : 'No requiere'),
            Column::make('Recepción', 'received_at')
                ->format(fn ($value): string => $value->format('d/m/Y')),
            Column::make('Acciones')
                ->label(fn (SecurityDeviceLot $row): View => view('pages.security-device-lots.partials.actions', ['lot' => $row])),
        ];
    }

    /**
     * Get the filters of the table: shipping line, provider, device type, approval and reception dates.
     *
     * @return array<int, Filter>
     */
    public function filters(): array
    {
        return [
            SelectFilter::make('Aprobación', 'approval')
                ->options(['' => 'Todos', 'none' => 'Sin aprobar', 'partial' => 'A medias', 'full' => 'Todo aprobado', 'not_required' => 'No requiere'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $this->filterByApproval($builder, $value)),
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
     * Keep the lots with none, some or all of their devices approved (H29, criterion 12), or the ones whose type does
     * not require approval (T62), which are left out of the other three.
     *
     * @param  Builder<SecurityDeviceLot>  $builder
     * @return Builder<SecurityDeviceLot>
     */
    private function filterByApproval(Builder $builder, string $value): Builder
    {
        $approved = fn (Builder $devices) => $devices->where('status', '!=', SecurityDeviceStatus::Inactive->value);
        $inactive = fn (Builder $devices) => $devices->where('status', SecurityDeviceStatus::Inactive->value);

        if ($value === 'not_required') {
            return $this->whereTypeRequiresApproval($builder, false);
        }

        if (! in_array($value, ['none', 'partial', 'full'], true)) {
            return $builder;
        }

        $builder = $this->whereTypeRequiresApproval($builder, true);

        return match ($value) {
            'none' => $builder->whereDoesntHave('devices', $approved),
            'partial' => $builder->whereHas('devices', $approved)->whereHas('devices', $inactive),
            default => $builder->whereDoesntHave('devices', $inactive),
        };
    }

    /**
     * Keep the lots whose type requires, or not, the approval of their devices (T62).
     *
     * @param  Builder<SecurityDeviceLot>  $builder
     * @return Builder<SecurityDeviceLot>
     */
    private function whereTypeRequiresApproval(Builder $builder, bool $requiresApproval): Builder
    {
        return $builder->whereHas(
            'announcement.securityDeviceType',
            fn (Builder $type) => $type->where('requires_lot_approval', $requiresApproval),
        );
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
