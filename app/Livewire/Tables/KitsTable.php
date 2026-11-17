<?php

namespace App\Livewire\Tables;

use App\Enums\KitStatus;
use App\Models\Kit;
use App\Models\KitType;
use App\Models\ShippingLine;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

/**
 * The list of kits (H14 and H16): to find a kit by its pouch, typed or scanned, or by its filters, and open it.
 */
class KitsTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects([
            'kits.id as id',
            'kits.kit_type_id as kit_type_id',
            'kits.shipping_line_id as shipping_line_id',
            'kits.created_at as created_at',
        ])
            ->setSearchPlaceholder('Funda')
            ->setEmptyMessage('Todavía no hay kits. Crea el primero con «Nuevo».')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.kits.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, the newest kits first, with how many devices each one has and asks for.
     *
     * @return Builder<Kit>
     */
    public function builder(): Builder
    {
        return Kit::query()
            ->with(['kitType', 'shippingLine'])
            ->withCount('devices')
            ->withSum('securityDeviceTypes as devices_quantity', 'kit_security_device_type.quantity')
            ->orderByDesc('kits.id');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Funda', 'pouch_code')->searchable(),
            Column::make('Tipo de kit')
                ->label(fn (Kit $row): string => $row->kitType->name),
            Column::make('Naviera')
                ->label(fn (Kit $row): string => $row->shippingLine->code),
            Column::make('Semana')
                ->label(fn (Kit $row): string => $row->week()),
            Column::make('Dispositivos')
                ->label(fn (Kit $row): string => sprintf('%d de %d', $row->getAttribute('devices_count'), $row->getAttribute('devices_quantity'))),
            Column::make('Estado', 'status')
                ->format(fn ($value, Kit $row): View => view('pages.kits.partials.status', ['kit' => $row])),
            Column::make('Acciones')
                ->label(fn (Kit $row): View => view('pages.kits.partials.actions', ['kit' => $row])),
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
            SelectFilter::make('Tipo de kit', 'kit_type')
                ->options(['' => 'Todos'] + KitType::withTrashed()->orderBy('name')->pluck('name', 'id')->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('kits.kit_type_id', (int) $value)),
            SelectFilter::make('Naviera', 'shipping_line')
                ->options(['' => 'Todas'] + ShippingLine::withTrashed()->orderBy('code')->pluck('code', 'id')->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('kits.shipping_line_id', (int) $value)),
            $this->weekFilter('kits.created_at'),
            SelectFilter::make('Estado', 'status')
                ->options(['' => 'Todos'] + collect(KitStatus::cases())->mapWithKeys(fn (KitStatus $status): array => [$status->value => $status->label()])->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('kits.status', $value)),
        ];
    }
}
