<?php

namespace App\Livewire\Tables;

use App\Models\ShippingLine;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class ShippingLinesTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects(['shipping_lines.id as id'])
            ->setSearchPlaceholder('Código o nombre')
            ->setEmptyMessage('No se encontraron líneas navieras.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.shipping-lines.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, with the deactivated shipping lines too, ordered by code.
     *
     * @return Builder<ShippingLine>
     */
    public function builder(): Builder
    {
        return ShippingLine::withTrashed()->orderBy('code');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Código', 'code')->searchable(),
            Column::make('Nombre', 'name')->searchable(),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, ShippingLine $row): View => view('pages.shipping-lines.partials.status', ['shippingLine' => $row])),
            Column::make('Acciones')
                ->label(fn (ShippingLine $row): View => view('pages.shipping-lines.partials.actions', ['shippingLine' => $row])),
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
                ->options(['' => 'Todas', 'active' => 'Activa', 'inactive' => 'Inactiva'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $value === 'active'
                    ? $builder->whereNull('deleted_at')
                    : $builder->whereNotNull('deleted_at')),
        ];
    }

    /**
     * Deactivate the given shipping line.
     */
    public function deactivate(int $id): void
    {
        $shippingLine = $this->builder()->findOrFail($id);

        if ($shippingLine->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Línea naviera ya desactivada', text: 'Esta línea naviera ya está desactivada.');

            return;
        }

        $shippingLine->delete();

        $this->dispatch('toast', icon: 'success', title: 'Línea naviera desactivada', text: 'La línea naviera se desactivó correctamente.');
    }

    /**
     * Reactivate the given shipping line.
     */
    public function reactivate(int $id): void
    {
        $shippingLine = $this->builder()->findOrFail($id);

        if (! $shippingLine->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Línea naviera ya activa', text: 'Esta línea naviera ya está activa.');

            return;
        }

        $shippingLine->restore();

        $this->dispatch('toast', icon: 'success', title: 'Línea naviera reactivada', text: 'La línea naviera se reactivó correctamente.');
    }
}
