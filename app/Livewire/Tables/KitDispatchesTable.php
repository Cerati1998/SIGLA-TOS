<?php

namespace App\Livewire\Tables;

use App\Models\KitDispatch;
use App\Models\KitType;
use App\Models\Worker;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

/**
 * The list of the dispatches of kits (H15, criterion 8). H16 adds the detail and the bulk dispatches.
 */
class KitDispatchesTable extends BaseTable
{
    /**
     * How many weeks back the week filter offers.
     */
    public const int WEEKS = 12;

    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects([
            'kit_dispatches.id as id',
            'kit_dispatches.receiver_id as receiver_id',
            'kit_dispatches.created_by as created_by',
            'kit_dispatches.created_at as created_at',
        ])
            ->setSearchPlaceholder('Número o trabajador')
            ->setEmptyMessage('Todavía no hay despachos. Usa «Despachar kits» para registrar el primero.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.kit-dispatches.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, the newest dispatches first, with what the columns read.
     *
     * @return Builder<KitDispatch>
     */
    public function builder(): Builder
    {
        return KitDispatch::query()
            ->with(['receiver', 'creator', 'kits.kitType'])
            ->orderByDesc('kit_dispatches.id');
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
                ->format(fn ($value, KitDispatch $row): string => $row->number())
                ->searchable(fn (Builder $query, string $search) => $query
                    ->orWhere('kit_dispatches.id', self::dispatchIdFrom($search))
                    ->orWhereHas('receiver', fn (Builder $query) => $query->where('full_name', 'like', '%'.trim($search).'%'))),
            Column::make('Fecha')
                ->label(fn (KitDispatch $row): string => ($row->created_at ?? now())->timezone('America/Lima')->format('d/m/Y H:i')),
            Column::make('Semana')
                ->label(fn (KitDispatch $row): string => $row->week()),
            Column::make('Trabajador que recibe')
                ->label(fn (KitDispatch $row): string => $row->receiver->full_name),
            Column::make('Kits')
                ->label(fn (KitDispatch $row): View => view('pages.kit-dispatches.partials.kits', ['kitDispatch' => $row])),
            Column::make('Despachó')
                ->label(fn (KitDispatch $row): string => $row->creator->name),
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
            SelectFilter::make('Semana', 'week')
                ->options(['' => 'Todas'] + self::weekOptions())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(function (Builder $builder, string $value): void {
                    [$start, $end] = self::weekRange($value);
                    $builder->whereBetween('kit_dispatches.created_at', [$start, $end]);
                }),
            SelectFilter::make('Trabajador que recibe', 'receiver')
                ->options(['' => 'Todos'] + Worker::withTrashed()
                    ->whereIn('id', KitDispatch::query()->select('receiver_id'))
                    ->orderBy('full_name')
                    ->pluck('full_name', 'id')
                    ->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('kit_dispatches.receiver_id', (int) $value)),
            SelectFilter::make('Tipo de kit', 'kit_type')
                ->options(['' => 'Todos'] + KitType::withTrashed()->orderBy('name')->pluck('name', 'id')->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->whereHas('kits', fn (Builder $query) => $query->where('kits.kit_type_id', (int) $value))),
        ];
    }

    /**
     * Get the last weeks, in Lima time, keyed as in 2026-W47 and labeled with their Monday and Sunday.
     *
     * @return array<string, string>
     */
    public static function weekOptions(): array
    {
        $monday = now('America/Lima')->startOfWeek();
        $options = [];

        for ($weeksBack = 0; $weeksBack < self::WEEKS; $weeksBack++) {
            $start = $monday->copy()->subWeeks($weeksBack);
            $options[$start->format('o-\WW')] = sprintf('%s (%s al %s)', $start->format('o-\WW'), $start->format('d/m'), $start->copy()->endOfWeek()->format('d/m'));
        }

        return $options;
    }

    /**
     * Get the start and the end, in the time the app saves (UTC), of an ISO week of Lima such as 2026-W47.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function weekRange(string $week): array
    {
        [$year, $number] = array_map('intval', explode('-W', $week) + [1 => 1]);
        $start = Carbon::now('America/Lima')->setISODate($year, $number)->startOfWeek();

        return [$start->copy()->utc(), $start->copy()->endOfWeek()->utc()];
    }

    /**
     * Get the id a search like D-000013 or 13 points to, or 0 when the search is not a number.
     */
    private static function dispatchIdFrom(string $search): int
    {
        $digits = preg_replace('/^\s*D-?/i', '', trim($search)) ?? '';

        return ctype_digit($digits) ? (int) $digits : 0;
    }
}
