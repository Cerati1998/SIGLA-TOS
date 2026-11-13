<?php

namespace App\Livewire\Tables;

use App\Models\Worker;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class WorkersTable extends BaseTable
{
    /**
     * Configure the table. The search stays out of the URL, because it can hold a document number.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects([
            'workers.id as id',
            'workers.identity_id as identity_id',
            'workers.is_own as is_own',
            'workers.provider_id as provider_id',
        ])
            ->setQueryStringStatus(false)
            ->setSearchPlaceholder('Nombre o número de documento')
            ->setEmptyMessage('No se encontraron trabajadores.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.workers.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, with the deactivated workers too, ordered by full name.
     *
     * @return Builder<Worker>
     */
    public function builder(): Builder
    {
        return Worker::withTrashed()->with(['identity', 'provider'])->orderBy('full_name');
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
                ->format(fn ($value, Worker $row): View => view('pages.workers.partials.document', ['worker' => $row])),
            Column::make('Nombres y apellidos', 'full_name')->searchable(),
            Column::make('Cargo', 'position')
                ->format(fn (?string $value): string => $value ?? '—'),
            Column::make('Procedencia')
                ->label(fn (Worker $row): View => view('pages.workers.partials.origin', ['worker' => $row])),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, Worker $row): View => view('pages.workers.partials.status', ['worker' => $row])),
            Column::make('Acciones')
                ->label(fn (Worker $row): View => view('pages.workers.partials.actions', ['worker' => $row])),
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
            SelectFilter::make('Procedencia', 'origin')
                ->options(['' => 'Todas', 'own' => 'Propio', 'provider' => 'De un proveedor'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('workers.is_own', $value === 'own')),
            SelectFilter::make('Estado', 'status')
                ->options(['' => 'Todos', 'active' => 'Activo', 'inactive' => 'Inactivo'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $value === 'active'
                    ? $builder->whereNull('workers.deleted_at')
                    : $builder->whereNotNull('workers.deleted_at')),
        ];
    }

    /**
     * Deactivate the given worker.
     */
    public function deactivate(int $id): void
    {
        $worker = Worker::withTrashed()->findOrFail($id);

        if ($worker->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Trabajador ya desactivado', text: 'Este trabajador ya está desactivado.');

            return;
        }

        $worker->delete();

        $this->dispatch('toast', icon: 'success', title: 'Trabajador desactivado', text: 'El trabajador se desactivó correctamente.');
    }

    /**
     * Reactivate the given worker.
     */
    public function reactivate(int $id): void
    {
        $worker = Worker::withTrashed()->findOrFail($id);

        if (! $worker->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Trabajador ya activo', text: 'Este trabajador ya está activo.');

            return;
        }

        $worker->restore();

        $this->dispatch('toast', icon: 'success', title: 'Trabajador reactivado', text: 'El trabajador se reactivó correctamente.');
    }
}
