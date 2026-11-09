<?php

namespace App\Livewire\Tables;

use App\Models\Provider;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class ProvidersTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects(['providers.id as id', 'providers.identity_id as identity_id'])
            ->setSearchPlaceholder('Razón social o documento')
            ->setEmptyMessage('No se encontraron proveedores.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.providers.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, with the deactivated providers too, ordered by business name.
     *
     * @return Builder<Provider>
     */
    public function builder(): Builder
    {
        return Provider::withTrashed()->orderBy('business_name');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Documento', 'document_number')
                ->searchable()
                ->format(fn ($value, Provider $row): View => view('pages.providers.partials.document', ['provider' => $row])),
            Column::make('Razón social', 'business_name')->searchable(),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, Provider $row): View => view('pages.providers.partials.status', ['provider' => $row])),
            Column::make('Acciones')
                ->label(fn (Provider $row): View => view('pages.providers.partials.actions', ['provider' => $row])),
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
                ->options(['' => 'Todos', 'active' => 'Activo', 'inactive' => 'Inactivo'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $value === 'active'
                    ? $builder->whereNull('deleted_at')
                    : $builder->whereNotNull('deleted_at')),
        ];
    }

    /**
     * Deactivate the given provider.
     */
    public function deactivate(int $id): void
    {
        $provider = $this->builder()->findOrFail($id);

        if ($provider->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Proveedor ya desactivado', text: 'Este proveedor ya está desactivado.');

            return;
        }

        $provider->delete();

        $this->dispatch('toast', icon: 'success', title: 'Proveedor desactivado', text: 'El proveedor se desactivó correctamente.');
    }

    /**
     * Reactivate the given provider.
     */
    public function reactivate(int $id): void
    {
        $provider = $this->builder()->findOrFail($id);

        if (! $provider->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Proveedor ya activo', text: 'Este proveedor ya está activo.');

            return;
        }

        $provider->restore();

        $this->dispatch('toast', icon: 'success', title: 'Proveedor reactivado', text: 'El proveedor se reactivó correctamente.');
    }
}
