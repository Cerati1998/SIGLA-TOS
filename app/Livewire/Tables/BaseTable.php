<?php

namespace App\Livewire\Tables;

use Rappasoft\LaravelLivewireTables\DataTableComponent;

abstract class BaseTable extends DataTableComponent
{
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
}
