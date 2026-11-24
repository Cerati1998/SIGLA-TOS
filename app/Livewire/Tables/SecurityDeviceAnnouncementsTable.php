<?php

namespace App\Livewire\Tables;

use App\Models\Provider;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\ShippingLine;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

/**
 * The list of the announcements of the entry of lots (H37, criterion 9).
 */
class SecurityDeviceAnnouncementsTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects([
            'security_device_announcements.id as id',
            'security_device_announcements.shipping_line_id as shipping_line_id',
            'security_device_announcements.provider_id as provider_id',
        ])
            ->setSearchPlaceholder('Guía o serie')
            ->setEmptyMessage('No se encontraron anuncios de ingreso de lotes.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.security-device-announcements.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, the latest receptions first, with how many lots and devices each announcement brings.
     *
     * @return Builder<SecurityDeviceAnnouncement>
     */
    public function builder(): Builder
    {
        return SecurityDeviceAnnouncement::query()
            ->with(['shippingLine', 'provider'])
            ->withCount('lots')
            ->withSum('lots as devices_count', 'quantity')
            ->orderByDesc('security_device_announcements.received_at')
            ->orderByDesc('security_device_announcements.id');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('N.º', 'id')
                ->format(fn ($value, SecurityDeviceAnnouncement $row): string => $row->number()),
            Column::make('Naviera')
                ->label(fn (SecurityDeviceAnnouncement $row): string => $row->shippingLine->code),
            Column::make('Proveedor')
                ->label(fn (SecurityDeviceAnnouncement $row): string => $row->provider->business_name),
            Column::make('Guía', 'dispatch_number')
                ->searchable(fn (Builder $query, string $search) => $this->search($query, $search)),
            Column::make('Recepción', 'received_at')
                ->format(fn ($value): string => $value->format('d/m/Y')),
            Column::make('Lotes')
                ->label(fn (SecurityDeviceAnnouncement $row): string => number_format((int) $row->getAttribute('lots_count'), 0, ',', ' ')),
            Column::make('Dispositivos')
                ->label(fn (SecurityDeviceAnnouncement $row): string => number_format((int) $row->getAttribute('devices_count'), 0, ',', ' ')),
            Column::make('Acciones')
                ->label(fn (SecurityDeviceAnnouncement $row): View => view('pages.security-device-announcements.partials.actions', ['announcement' => $row])),
        ];
    }

    /**
     * Get the filters of the table: shipping line, provider and reception dates.
     *
     * @return array<int, Filter>
     */
    public function filters(): array
    {
        return [
            SelectFilter::make('Naviera', 'shipping_line')
                ->options(['' => 'Todas'] + ShippingLine::withTrashed()->orderBy('code')->pluck('code', 'id')->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('security_device_announcements.shipping_line_id', (int) $value)),
            SelectFilter::make('Proveedor', 'provider')
                ->options(['' => 'Todos'] + Provider::withTrashed()->orderBy('business_name')->pluck('business_name', 'id')->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('security_device_announcements.provider_id', (int) $value)),
            DateFilter::make('Recepción desde', 'received_from')
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->whereDate('security_device_announcements.received_at', '>=', $value)),
            DateFilter::make('Recepción hasta', 'received_until')
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->whereDate('security_device_announcements.received_at', '<=', $value)),
        ];
    }

    /**
     * Find the announcements by their dispatch number or by the series of their lots: a whole serial number shows the
     * announcement whose lot brings it, and only the start of a series (GSW) the ones with lots of that series.
     *
     * @param  Builder<SecurityDeviceAnnouncement>  $query
     * @return Builder<SecurityDeviceAnnouncement>
     */
    private function search(Builder $query, string $search): Builder
    {
        $search = trim($search);

        return $query
            ->orWhere('security_device_announcements.dispatch_number', 'like', '%'.$search.'%')
            ->orWhereHas('lots', fn (Builder $lots) => $lots
                ->where('series_prefix', '!=', '')
                ->where('series_prefix', 'like', mb_strtoupper($search).'%'))
            ->orWhereHas('lots', fn (Builder $lots) => $lots->whereIn(
                'security_device_lots.id',
                SecurityDevice::query()->select('security_device_lot_id')->where('serial_number', $search),
            ));
    }
}
