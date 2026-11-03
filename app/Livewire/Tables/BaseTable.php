<?php

namespace App\Livewire\Tables;

use Rappasoft\LaravelLivewireTables\DataTableComponent;

abstract class BaseTable extends DataTableComponent
{
    /**
     * Apply the look of the template to the table and drop the package toolbar.
     */
    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setToolsDisabled()
            ->setColumnSelectDisabled()
            ->setPerPageVisibilityDisabled()
            ->setPaginationVisibilityDisabled()
            ->setTableWrapperAttributes(['default' => false, 'class' => 'overflow-x-auto'])
            ->setTableAttributes(['default' => false, 'class' => 'w-full min-w-160'])
            ->setTheadAttributes(['default' => false])
            ->setTbodyAttributes(['default' => false])
            ->setThAttributes(fn (): array => [
                'class' => 'border-b border-line bg-table-head p-3.5 text-start text-sm font-bold text-ink first:ps-5.25 last:pe-5.25',
            ])
            ->setTrAttributes(fn (): array => ['default' => false])
            ->setTdAttributes(fn (): array => [
                'default' => false,
                'class' => 'border-b border-line p-3.5 text-base text-ink first:ps-5.25 last:pe-5.25',
            ])
            ->setConfigurableAreas(['after-wrapper' => 'partials.panel.table-footer']);
    }
}
