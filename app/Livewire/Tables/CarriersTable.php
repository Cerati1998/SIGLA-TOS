<?php

namespace App\Livewire\Tables;

use App\Models\Carrier;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class CarriersTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects(['carriers.id as id'])
            ->setSearchPlaceholder('Razón social o RUC')
            ->setEmptyMessage('No se encontraron transportistas.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.carriers.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, with the deactivated carriers too, ordered by business name.
     *
     * @return Builder<Carrier>
     */
    public function builder(): Builder
    {
        return Carrier::withTrashed()->orderBy('business_name');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('RUC', 'document_number')->searchable(),
            Column::make('Razón social', 'business_name')->searchable(),
            Column::make('Teléfono', 'phone')
                ->format(fn (?string $value): string => $value ?? '—'),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, Carrier $row): View => view('pages.carriers.partials.status', ['carrier' => $row])),
            Column::make('Acciones')
                ->label(fn (Carrier $row): View => view('pages.carriers.partials.actions', ['carrier' => $row])),
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
                ->options(['' => 'Todos', 'active' => 'Activo', 'inactive' => 'Desactivado'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $value === 'active'
                    ? $builder->whereNull('deleted_at')
                    : $builder->whereNotNull('deleted_at')),
        ];
    }

    /**
     * Deactivate the given carrier.
     */
    public function deactivate(int $id): void
    {
        $carrier = $this->builder()->findOrFail($id);

        if ($carrier->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Transportista ya desactivado', text: 'Este transportista ya está desactivado.');

            return;
        }

        $carrier->delete();

        $this->dispatch('toast', icon: 'success', title: 'Transportista desactivado', text: 'El transportista se desactivó correctamente.');
    }

    /**
     * Reactivate the given carrier.
     */
    public function reactivate(int $id): void
    {
        $carrier = $this->builder()->findOrFail($id);

        if (! $carrier->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Transportista ya activo', text: 'Este transportista ya está activo.');

            return;
        }

        $carrier->restore();

        $this->dispatch('toast', icon: 'success', title: 'Transportista reactivado', text: 'El transportista se reactivó correctamente.');
    }
}
