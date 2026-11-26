<?php

namespace App\Livewire\Tables;

use App\Actions\Certifications\CloseCertification;
use App\Actions\Reinspections\CloseReinspection;
use App\Enums\CertificationStatus;
use App\Models\Certification;
use App\Models\ShippingLine;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Throwable;

/**
 * The list of assignments (H20): every assigned unit with how far its two inspections went, where it is closed and
 * reopened (H19), and where its reinspection is closed (H35). The state, the progress and the actions go first, so
 * they fit on the phone without scrolling.
 */
class CertificationsTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects([
            'certifications.id as id',
            'certifications.kit_id as kit_id',
            'certifications.closed_at as closed_at',
            'certifications.started_at as started_at',
        ])
            ->setSearchPlaceholder('Contenedor, booking o placa')
            ->setEmptyMessage('Todavía no hay asignaciones. Usa «Nueva asignación» para asignar el primer kit.')
            ->setConfigurableAreas([
                'toolbar-right-end' => 'pages.certifications.partials.options',
                'after-wrapper' => 'partials.panel.table-footer',
            ]);
    }

    /**
     * Get the query of the table, the units assigned last first, with the progress of their inspections.
     *
     * @return Builder<Certification>
     */
    public function builder(): Builder
    {
        return Certification::query()
            ->withInspectionProgress()
            ->with(['shippingLine', 'tractor', 'kit' => fn ($kits) => $kits->withCount('devices'), 'openReinspection.devices'])
            ->orderByDesc('certifications.started_at')
            ->orderByDesc('certifications.id');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Contenedor', 'container')
                ->format(fn (string $value): View => view('pages.certifications.partials.container', ['container' => $value]))
                ->searchable(fn (Builder $query, string $search) => $query
                    ->orWhere('certifications.container', 'like', '%'.self::compact($search).'%')
                    ->orWhere('certifications.booking', 'like', '%'.trim($search).'%')
                    ->orWhereHas('tractor', fn (Builder $vehicles) => $vehicles->where('plate', 'like', '%'.self::compact($search).'%'))
                    ->orWhereHas('semiTrailer', fn (Builder $vehicles) => $vehicles->where('plate', 'like', '%'.self::compact($search).'%'))),
            Column::make('Estado')
                ->label(fn (Certification $row): View => view('pages.certifications.partials.status', ['status' => $row->status()])),
            Column::make('Avance')
                ->label(fn (Certification $row): View => view('pages.certifications.partials.progress', ['progress' => $row->progress(), 'reinspection' => $row->openReinspection])),
            Column::make('Acciones')
                ->label(fn (Certification $row): View => view('pages.certifications.partials.actions', ['certification' => $row])),
            Column::make('Naviera')
                ->label(fn (Certification $row): string => $row->shippingLine->code),
            Column::make('Booking', 'booking'),
            Column::make('Placa')
                ->label(fn (Certification $row): string => $row->tractor->plate ?? '—'),
            Column::make('Asignada')
                ->label(fn (Certification $row): string => $row->started_at->timezone('America/Lima')->format('d/m/Y H:i')),
        ];
    }

    /**
     * Get the filters of the table (H20, criterion 1).
     *
     * @return array<int, Filter>
     */
    public function filters(): array
    {
        return [
            DateFilter::make('Fecha de asignación', 'started_on')
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(function (Builder $builder, string $value): void {
                    $day = Carbon::parse($value, 'America/Lima');
                    $builder->whereBetween('certifications.started_at', [$day->copy()->startOfDay()->utc(), $day->copy()->endOfDay()->utc()]);
                }),
            SelectFilter::make('Línea naviera', 'shipping_line')
                ->options(['' => 'Todas'] + ShippingLine::withTrashed()
                    ->whereIn('id', Certification::query()->select('shipping_line_id'))
                    ->orderBy('code')
                    ->pluck('code', 'id')
                    ->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('certifications.shipping_line_id', (int) $value)),
            SelectFilter::make('Inspector', 'inspector')
                ->options(['' => 'Todos'] + User::withTrashed()
                    ->whereIn('id', Certification::query()->select('certified_by'))
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->where('certifications.certified_by', (int) $value)),
            SelectFilter::make('Estado', 'status')
                ->options(['' => 'Todas'] + collect(CertificationStatus::cases())
                    ->mapWithKeys(fn (CertificationStatus $status): array => [$status->value => $status->label()])
                    ->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->whereStatus(CertificationStatus::from($value))),
        ];
    }

    /**
     * Get what was searched without spaces or dashes and in capitals, the way containers and plates are kept.
     */
    private static function compact(string $search): string
    {
        return mb_strtoupper((string) preg_replace('/[\s-]+/', '', $search));
    }

    /**
     * Close a certification once the inspector confirmed it, with its kit and devices as Usado (H19, criteria 1 to 5).
     */
    public function close(int $id, CloseCertification $closeCertification): void
    {
        /** @var Certification $certification */
        $certification = $this->builder()->findOrFail($id);
        /** @var User $user */
        $user = Auth::user();

        try {
            $closeCertification->handle($certification, $user);
        } catch (ValidationException $exception) {
            $this->dispatch('toast', icon: 'warning', title: 'No se puede cerrar', text: $exception->validator->errors()->first('certification'));

            return;
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatch('toast', icon: 'error', title: 'No se pudo cerrar', text: 'No se pudo cerrar la certificación y no se cambió nada. Intenta de nuevo.');

            return;
        }

        $this->dispatch('toast', icon: 'success', title: 'Certificación cerrada', text: "Certificación de {$certification->container} cerrada. Ya puedes descargar su PDF.");
    }

    /**
     * Close the reinspection of a certification once the inspector confirmed it: the unit goes back to the checkpoint
     * (H35, criterion 7).
     */
    public function closeReinspection(int $id, CloseReinspection $closeReinspection): void
    {
        /** @var Certification $certification */
        $certification = $this->builder()->findOrFail($id);
        /** @var User $user */
        $user = Auth::user();

        try {
            $closeReinspection->handle($certification, $user);
        } catch (ValidationException $exception) {
            $this->dispatch('toast', icon: 'warning', title: 'No se puede cerrar la reinspección', text: $exception->validator->errors()->first('reinspection'));

            return;
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatch('toast', icon: 'error', title: 'No se pudo cerrar la reinspección', text: 'No se cerró y no se cambió nada. Intenta de nuevo.');

            return;
        }

        $this->dispatch('toast', icon: 'success', title: 'Reinspección cerrada', text: "{$certification->container} vuelve al checkpoint, que verifica solo las series reemplazadas.");
    }
}
