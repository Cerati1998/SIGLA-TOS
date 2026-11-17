<?php

namespace App\Livewire\Tables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

abstract class BaseTable extends DataTableComponent
{
    /**
     * How many weeks back a week filter offers.
     */
    public const int WEEKS = 12;

    /**
     * Classes of the controls of the toolbar and the filters.
     */
    protected const CONTROL_CLASSES = 'block h-10.25 w-full rounded-control border border-line bg-white px-3 text-sm text-ink outline-hidden focus:border-brand max-md:h-11';

    /**
     * Attributes that replace the default look of a control of the package.
     *
     * @var array<string, bool|string>
     */
    protected const CONTROL_ATTRIBUTES = [
        'default' => false,
        'default-styling' => false,
        'default-colors' => false,
        'class' => self::CONTROL_CLASSES,
    ];

    /**
     * Apply the theme of the package with the colors of the brand.
     */
    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setColumnSelectDisabled()
            ->setSortingPillsDisabled()
            ->setSearchDebounce(300)
            ->setPerPageAccepted([10, 25, 50])
            ->setPaginationVisibilityDisabled()
            ->setToolsAttributes(['default-styling' => false, 'class' => 'border-b border-line p-5.25'])
            ->setToolBarAttributes(['default-styling' => false, 'class' => 'md:flex md:justify-between'])
            ->setSearchFieldAttributes(self::CONTROL_ATTRIBUTES)
            ->setPerPageFieldAttributes(self::CONTROL_ATTRIBUTES)
            ->setFilterButtonAttributes([
                'default-styling' => false,
                'default-colors' => false,
                'class' => 'inline-flex h-10.25 w-full cursor-pointer items-center justify-center rounded-control border border-line bg-white px-4 text-sm font-medium text-ink hover:bg-surface max-md:h-11',
            ])
            ->setFilterPopoverAttributes([
                'default-styling' => false,
                'default-colors' => false,
                'class' => 'absolute left-0 z-50 mt-2 origin-top-left divide-y divide-line rounded-control border border-line bg-white shadow-menu',
            ])
            ->setTableWrapperAttributes(['default' => false, 'class' => 'overflow-x-auto'])
            ->setTableAttributes(['default' => false, 'class' => 'w-full min-w-160'])
            ->setTheadAttributes(['default' => false])
            ->setTbodyAttributes(['default' => false])
            ->setThAttributes(fn (): array => [
                'class' => 'whitespace-nowrap border-b border-line bg-table-head px-6 py-3 text-start text-xs font-medium tracking-wider text-ink-muted uppercase first:ps-5.25 last:pe-5.25',
            ])
            ->setTrAttributes(fn ($row, int $index): array => [
                'default' => false,
                'class' => $index % 2 === 0 ? 'bg-white' : 'bg-surface',
            ])
            ->setTdAttributes(fn (): array => [
                'default' => false,
                'class' => 'whitespace-nowrap border-b border-line px-6 py-4 text-sm text-ink first:ps-5.25 last:pe-5.25',
            ])
            ->setConfigurableAreas(['after-wrapper' => 'partials.panel.table-footer']);
    }

    /**
     * Get a filter by the ISO week, in Lima time, of a date column, such as kit_dispatches.created_at.
     */
    protected function weekFilter(string $column): Filter
    {
        return SelectFilter::make('Semana', 'week')
            ->options(['' => 'Todas'] + self::weekOptions())
            ->setInputAttributes(self::CONTROL_ATTRIBUTES)
            ->filter(function (Builder $builder, string $value) use ($column): void {
                [$start, $end] = self::weekRange($value);
                $builder->whereBetween($column, [$start, $end]);
            });
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
}
