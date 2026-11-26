<?php

namespace App\Livewire\Tables;

use App\Enums\CheckpointStatus;
use App\Models\Certification;
use App\Models\Checkpoint;
use App\Models\CheckpointMark;
use App\Models\CheckpointPhoto;
use App\Models\ShippingLine;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

/**
 * The list of checkpoints (H36, criteria 1 and 2): every checkpoint, the ones started last first, with its status, how
 * far it went and who works on it. Only to read: the checkpoint is done in «Registrar checkpoint». The state, the
 * progress and the action go first, so they fit on the phone without scrolling, as in the list of assignments.
 */
class CheckpointsTable extends BaseTable
{
    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects([
            'checkpoints.id as id',
            'checkpoints.certification_id as certification_id',
            'checkpoints.started_by as started_by',
            'checkpoints.closed_at as closed_at',
            'checkpoints.closed_by as closed_by',
            'checkpoints.created_at as created_at',
        ])
            ->setSearchPlaceholder('Contenedor, booking o placa')
            ->setEmptyMessage('Todavía no hay checkpoints. Nacen al abrir una unidad en «Registrar checkpoint».');
    }

    /**
     * Get the query of the table, the checkpoints started last first, with what their progress, their inspector and
     * their last change are worked out from.
     *
     * @return Builder<Checkpoint>
     */
    public function builder(): Builder
    {
        return Checkpoint::query()
            ->withExists('openReinspection')
            ->with([
                'certification:id,container,booking,shipping_line_id,tractor_id',
                'certification.shippingLine:id,code',
                'certification.tractor:id,plate',
                'starter:id,name',
                'closer:id,name',
                'photos:id,checkpoint_id,uploaded_by,created_at',
                'photos.uploader:id,name',
                'steps:id,checkpoint_id,kind',
                'steps.photos:id,checkpoint_step_id',
                'steps.marks:id,checkpoint_step_id,is_correct,marked_by,marked_at',
                'steps.marks.marker:id,name',
                'reinspections:id,checkpoint_id,created_at,closed_at',
            ])
            ->orderByDesc('checkpoints.created_at')
            ->orderByDesc('checkpoints.id');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Contenedor')
                ->label(fn (Checkpoint $row): View => view('pages.certifications.partials.container', ['container' => $row->certification->container]))
                ->searchable(fn (Builder $query, string $search) => $query->orWhereHas('certification', fn (Builder $certifications) => $certifications
                    ->where('certifications.container', 'like', '%'.self::compact($search).'%')
                    ->orWhere('certifications.booking', 'like', '%'.trim($search).'%')
                    ->orWhereHas('tractor', fn (Builder $vehicles) => $vehicles->where('plate', 'like', '%'.self::compact($search).'%'))
                    ->orWhereHas('semiTrailer', fn (Builder $vehicles) => $vehicles->where('plate', 'like', '%'.self::compact($search).'%')))),
            Column::make('Estado')
                ->label(fn (Checkpoint $row): View => view('pages.certifications.partials.status', ['status' => $row->status()])),
            Column::make('Avance')
                ->label(fn (Checkpoint $row): View => view('pages.checkpoints.partials.progress', ['checkpoint' => $row])),
            Column::make('Acciones')
                ->label(fn (Checkpoint $row): View => view('pages.checkpoints.partials.actions', ['checkpoint' => $row])),
            Column::make('Naviera')
                ->label(fn (Checkpoint $row): string => $row->certification->shippingLine->code),
            Column::make('Booking')
                ->label(fn (Checkpoint $row): string => $row->certification->booking),
            Column::make('Placa')
                ->label(fn (Checkpoint $row): string => $row->certification->tractor->plate ?? '—'),
            Column::make('Inspector')
                ->label(fn (Checkpoint $row): string => $row->inspectorName() ?? '—'),
            Column::make('Último cambio')
                ->label(fn (Checkpoint $row): string => $row->lastChangeAt()->timezone('America/Lima')->format('d/m/Y H:i')),
        ];
    }

    /**
     * Get the filters of the table (criterion 1).
     *
     * @return array<int, Filter>
     */
    public function filters(): array
    {
        return [
            DateFilter::make('Fecha del checkpoint', 'started_on')
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(function (Builder $builder, string $value): void {
                    $day = Carbon::parse($value, 'America/Lima');
                    $builder->whereBetween('checkpoints.created_at', [$day->copy()->startOfDay()->utc(), $day->copy()->endOfDay()->utc()]);
                }),
            SelectFilter::make('Línea naviera', 'shipping_line')
                ->options(['' => 'Todas'] + ShippingLine::withTrashed()
                    ->whereIn('id', Certification::query()->has('checkpoint')->select('shipping_line_id'))
                    ->orderBy('code')
                    ->pluck('code', 'id')
                    ->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->whereHas('certification', fn (Builder $certifications) => $certifications->where('certifications.shipping_line_id', (int) $value))),
            SelectFilter::make('Inspector', 'inspector')
                ->options(['' => 'Todos'] + User::withTrashed()
                    ->where(fn (Builder $users) => $users
                        ->whereIn('id', Checkpoint::query()->select('started_by'))
                        ->orWhereIn('id', Checkpoint::query()->select('closed_by'))
                        ->orWhereIn('id', CheckpointPhoto::query()->select('uploaded_by'))
                        ->orWhereIn('id', CheckpointMark::query()->select('marked_by')))
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->whereIn('checkpoints.id', Checkpoint::query()->workedOnBy((int) $value)->select('checkpoints.id'))),
            SelectFilter::make('Estado', 'status')
                ->options(['' => 'Todos'] + collect(CheckpointStatus::cases())
                    ->mapWithKeys(fn (CheckpointStatus $status): array => [$status->value => $status->label()])
                    ->all())
                ->setInputAttributes(self::CONTROL_ATTRIBUTES)
                ->filter(fn (Builder $builder, string $value) => $builder->whereStatus(CheckpointStatus::from($value))),
        ];
    }

    /**
     * Get what was searched without spaces or dashes and in capitals, the way containers and plates are kept.
     */
    private static function compact(string $search): string
    {
        return mb_strtoupper((string) preg_replace('/[\s-]+/', '', $search));
    }
}
