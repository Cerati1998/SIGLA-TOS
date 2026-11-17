<?php

namespace App\Livewire\Tables;

use App\Enums\PaymentCondition;
use App\Models\Client;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class ClientsTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects(['clients.id as id', 'clients.identity_id as identity_id', 'clients.credit_days as credit_days'])
            ->setSearchPlaceholder('Razón social o documento')
            ->setEmptyMessage('No se encontraron clientes.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.clients.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, with the deactivated clients too, ordered by business name.
     *
     * @return Builder<Client>
     */
    public function builder(): Builder
    {
        return Client::withTrashed()->orderBy('business_name');
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
                ->format(fn ($value, Client $row): View => view('pages.clients.partials.document', ['client' => $row])),
            Column::make('Razón social', 'business_name')->searchable(),
            Column::make('Condición', 'payment_condition')
                ->format(fn (PaymentCondition $value, Client $row): string => $value === PaymentCondition::Credit
                    ? trans_choice('{1} Crédito · :count día|[0,*] Crédito · :count días', (int) $row->credit_days)
                    : $value->label()),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, Client $row): View => view('pages.clients.partials.status', ['client' => $row])),
            Column::make('Acciones')
                ->label(fn (Client $row): View => view('pages.clients.partials.actions', ['client' => $row])),
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
     * Deactivate the given client.
     */
    public function deactivate(int $id): void
    {
        $client = $this->builder()->findOrFail($id);

        if ($client->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Cliente ya desactivado', text: 'Este cliente ya está desactivado.');

            return;
        }

        $client->delete();

        $this->dispatch('toast', icon: 'success', title: 'Cliente desactivado', text: 'El cliente se desactivó correctamente.');
    }

    /**
     * Reactivate the given client.
     */
    public function reactivate(int $id): void
    {
        $client = $this->builder()->findOrFail($id);

        if (! $client->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Cliente ya activo', text: 'Este cliente ya está activo.');

            return;
        }

        $client->restore();

        $this->dispatch('toast', icon: 'success', title: 'Cliente reactivado', text: 'El cliente se reactivó correctamente.');
    }
}
