<?php

namespace App\Livewire\Tables;

use App\Actions\Certifications\CloseCertification;
use App\Models\Certification;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Throwable;

/**
 * The list of assignments (H20): every assigned unit with how far its two inspections went, where it is closed and
 * reopened (H19). The state, the progress and the actions go first, so they fit on the phone without scrolling.
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
            ->setSearchDisabled()
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
            ->with(['shippingLine', 'tractor', 'kit' => fn ($kits) => $kits->withCount('devices')])
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
                ->format(fn (string $value): View => view('pages.certifications.partials.container', ['container' => $value])),
            Column::make('Estado')
                ->label(fn (Certification $row): View => view('pages.certifications.partials.status', ['status' => $row->status()])),
            Column::make('Avance')
                ->label(fn (Certification $row): View => view('pages.certifications.partials.progress', ['progress' => $row->progress()])),
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

        $this->dispatch('toast', icon: 'success', title: 'Certificación cerrada', text: "Certificación de {$certification->container} cerrada.");
    }
}
