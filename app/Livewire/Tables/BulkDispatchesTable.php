<?php

namespace App\Livewire\Tables;

use App\Enums\BulkDispatchReason;
use App\Models\BulkDispatch;
use App\Models\YardZone;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

/**
 * The list of the bulk dispatches (H30, criterion 1), apart from the dispatches of kits.
 */
class BulkDispatchesTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects([
            'bulk_dispatches.id as id',
            'bulk_dispatches.receiver_id as receiver_id',
            'bulk_dispatches.created_by as created_by',
            'bulk_dispatches.reason as reason',
            'bulk_dispatches.client_id as client_id',
            'bulk_dispatches.kit_id as kit_id',
            'bulk_dispatches.yard_zone_id as yard_zone_id',
            'bulk_dispatches.created_at as created_at',
        ])
            ->setSearchPlaceholder('Número o trabajador')
            ->setEmptyMessage('Todavía no hay despachos a granel. Usa «Despachar a granel» para registrar el primero.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.bulk-dispatches.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, the newest dispatches first, with what the columns read.
     *
     * @return Builder<BulkDispatch>
     */
    public function builder(): Builder
    {
        return BulkDispatch::query()
            ->with(['receiver', 'creator', 'client', 'kit', 'yardZone'])
            ->withCount('devices')
            ->orderByDesc('bulk_dispatches.id');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Despacho', 'id')
                ->format(fn ($value, BulkDispatch $row): string => $row->number())
                ->searchable(fn (Builder $query, string $search) => $query
                    ->orWhere('bulk_dispatches.id', BulkDispatch::idFromNumber($search))
                    ->orWhereHas('receiver', fn (Builder $query) => $query->where('full_name', 'like', '%'.trim($search).'%'))),
            Column::make('Fecha')
                ->label(fn (BulkDispatch $row): string => ($row->created_at ?? now())->timezone('America/Lima')->format('d/m/Y H:i')),
            Column::make('Semana')
                ->label(fn (BulkDispatch $row): string => $row->week()),
            Column::make('Trabajador que recibe')
                ->label(fn (BulkDispatch $row): string => $row->receiver->full_name),
            Column::make('Motivo')
                ->label(fn (BulkDispatch $row): View => view('pages.bulk-dispatches.partials.reason', ['bulkDispatch' => $row])),
            Column::make('Patio de destino')
                ->label(fn (BulkDispatch $row): string => $row->yardZone->name),
            Column::make('Dispositivos')
                ->label(fn (BulkDispatch $row): View => view('pages.bulk-dispatches.partials.devices', [
                    'count' => (int) $row->getAttribute('devices_count'),
                    'summary' => $row->typeSummary(),
                ])),
            Column::make('Despachó')
                ->label(fn (BulkDispatch $row): string => $row->creator->name),
            Column::make('Acciones')
                ->label(fn (BulkDispatch $row): View => view('pages.bulk-dispatches.partials.actions', ['bulkDispatch' => $row])),
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
            $this->weekFilter('bulk_dispatches.created_at'),
            SelectFilter::make('Motivo de salida', 'reason')
                ->options(['' => 'Todos'] + collect(BulkDispatchReason::cases())->mapWithKeys(fn (BulkDispatchReason $reason): array => [$reason->value => $reason->label()])->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('bulk_dispatches.reason', $value)),
            SelectFilter::make('Patio de destino', 'yard_zone')
                ->options(['' => 'Todos'] + YardZone::withTrashed()
                    ->whereIn('id', BulkDispatch::query()->select('yard_zone_id'))
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('bulk_dispatches.yard_zone_id', (int) $value)),
        ];
    }
}
