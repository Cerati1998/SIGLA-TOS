<?php

namespace App\Livewire\Tables;

use App\Enums\CertificationSectionType;
use App\Models\CertificationSection;
use App\Services\CertificationSectionOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class CertificationSectionsTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects(['certification_sections.id as id', 'certification_sections.type as type'])
            ->setSearchPlaceholder('Buscar por nombre')
            ->setEmptyMessage(CertificationSection::withTrashed()->exists()
                ? 'No encontramos secciones con estos filtros.'
                : 'Todavía no hay secciones. Crea la primera con «Nueva sección».')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.certification-sections.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table: the active sections in their order, then the deactivated ones by name.
     *
     * @return Builder<CertificationSection>
     */
    public function builder(): Builder
    {
        return CertificationSection::withTrashed()
            ->orderByRaw('certification_sections.sort_order is null')
            ->orderBy('certification_sections.sort_order')
            ->orderBy('certification_sections.name');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Orden', 'sort_order')
                ->format(fn ($value, CertificationSection $row): View => view('pages.certification-sections.partials.position', ['certificationSection' => $row])),
            Column::make('Nombre', 'name')->searchable(),
            Column::make('Tipo', 'type')
                ->format(fn ($value, CertificationSection $row): View => view('pages.certification-sections.partials.type', ['certificationSection' => $row])),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, CertificationSection $row): View => view('pages.certification-sections.partials.status', ['certificationSection' => $row])),
            Column::make('Acciones')
                ->label(fn (CertificationSection $row): View => view('pages.certification-sections.partials.actions', ['certificationSection' => $row])),
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
            SelectFilter::make('Tipo', 'type')
                ->options(['' => 'Todos', ...CertificationSectionType::options()])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('certification_sections.type', $value)),
            SelectFilter::make('Estado', 'status')
                ->options(['' => 'Todos', 'active' => 'Activa', 'inactive' => 'Desactivada'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $value === 'active'
                    ? $builder->whereNull('certification_sections.deleted_at')
                    : $builder->whereNotNull('certification_sections.deleted_at')),
        ];
    }

    /**
     * Deactivate the given section, taking it out of the order.
     */
    public function deactivate(int $id, CertificationSectionOrder $order): void
    {
        $certificationSection = CertificationSection::withTrashed()->findOrFail($id);

        if ($certificationSection->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Sección ya desactivada', text: 'Esta sección ya está desactivada.');

            return;
        }

        $order->deactivate($certificationSection);

        $this->dispatch('toast', icon: 'success', title: 'Sección desactivada', text: 'Ya no aparecerá en certificaciones nuevas.');
    }

    /**
     * Reactivate the given section at the end of the order.
     */
    public function reactivate(int $id, CertificationSectionOrder $order): void
    {
        $certificationSection = CertificationSection::withTrashed()->findOrFail($id);

        if (! $certificationSection->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Sección ya activa', text: 'Esta sección ya está activa.');

            return;
        }

        $order->reactivate($certificationSection);

        $this->dispatch('toast', icon: 'success', title: 'Sección reactivada', text: 'Quedó al final del orden.');
    }
}
