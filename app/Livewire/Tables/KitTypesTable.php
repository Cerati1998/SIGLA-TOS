<?php

namespace App\Livewire\Tables;

use App\Models\ContainerType;
use App\Models\KitType;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class KitTypesTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects(['kit_types.id as id', 'kit_types.container_type_id as container_type_id'])
            ->setSearchPlaceholder('Buscar por nombre')
            ->setEmptyMessage(KitType::withTrashed()->exists()
                ? 'No encontramos tipos de kit con estos filtros.'
                : 'Todavía no hay tipos de kit. Crea el primero con «Nuevo».')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.kit-types.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, with the deactivated kit types too, ordered by name.
     *
     * @return Builder<KitType>
     */
    public function builder(): Builder
    {
        return KitType::withTrashed()->with(['containerType', 'securityDeviceTypes'])->orderBy('kit_types.name');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Nombre', 'name')->searchable(),
            Column::make('Tipo de contenedor')
                ->label(fn (KitType $row): string => $row->containerType->label()),
            Column::make('Contenido')
                ->label(fn (KitType $row): View => view('pages.kit-types.partials.contents', ['kitType' => $row])),
            Column::make('Estado', 'deleted_at')
                ->format(fn ($value, KitType $row): View => view('pages.kit-types.partials.status', ['kitType' => $row])),
            Column::make('Acciones')
                ->label(fn (KitType $row): View => view('pages.kit-types.partials.actions', ['kitType' => $row])),
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
            SelectFilter::make('Tipo de contenedor', 'container_type')
                ->options(['' => 'Todos'] + ContainerType::withTrashed()->orderBy('code')->get()
                    ->mapWithKeys(fn (ContainerType $type): array => [$type->id => $type->label()])
                    ->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('kit_types.container_type_id', $value)),
            SelectFilter::make('Estado', 'status')
                ->options(['' => 'Todos', 'active' => 'Activo', 'inactive' => 'Desactivado'])
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $value === 'active'
                    ? $builder->whereNull('kit_types.deleted_at')
                    : $builder->whereNotNull('kit_types.deleted_at')),
        ];
    }

    /**
     * Deactivate the given kit type: it no longer appears when building kits.
     */
    public function deactivate(int $id): void
    {
        $kitType = $this->builder()->findOrFail($id);

        if ($kitType->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Tipo de kit ya desactivado', text: 'Este tipo de kit ya está desactivado.');

            return;
        }

        $kitType->delete();

        $this->dispatch('toast', icon: 'success', title: 'Tipo de kit desactivado', text: 'El tipo de kit se desactivó correctamente.');
    }

    /**
     * Reactivate the given kit type.
     */
    public function reactivate(int $id): void
    {
        $kitType = $this->builder()->findOrFail($id);

        if (! $kitType->trashed()) {
            $this->dispatch('toast', icon: 'warning', title: 'Tipo de kit ya activo', text: 'Este tipo de kit ya está activo.');

            return;
        }

        $kitType->restore();

        $this->dispatch('toast', icon: 'success', title: 'Tipo de kit reactivado', text: 'El tipo de kit se reactivó correctamente.');
    }
}
