<?php

namespace App\Livewire\Tables;

use App\Actions\SecurityDevices\RemoveSecurityDeviceLot;
use App\Enums\SecurityDeviceStatus;
use App\Models\SecurityDeviceLot;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Locked;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * The lots of an announcement of the entry of lots, in its detail (H37, criterion 10).
 */
class SecurityDeviceLotsTable extends BaseTable
{
    #[Locked]
    public int $announcementId;

    /**
     * How many lots the announcement has, counted once per render.
     */
    private ?int $lotsCount = null;

    /**
     * Configure the table.
     */
    public function configure(): void
    {
        parent::configure();

        $this->setAdditionalSelects([
            'security_device_lots.id as id',
            'security_device_lots.security_device_announcement_id as security_device_announcement_id',
            'security_device_lots.security_device_type_id as security_device_type_id',
            'security_device_lots.series_prefix as series_prefix',
            'security_device_lots.min_digits as min_digits',
            'security_device_lots.series_start as series_start',
            'security_device_lots.series_end as series_end',
            'security_device_lots.series_suffix as series_suffix',
        ])
            ->setSearchDisabled()
            ->setFiltersDisabled()
            ->setEmptyMessage('Este anuncio no tiene lotes.')
            ->setToolsAttributes(['default-styling' => false, 'class' => 'border-b border-line p-5.25 max-md:hidden'])
            ->setTableWrapperAttributes(['default' => false, 'class' => 'overflow-x-auto max-md:hidden'])
            ->setConfigurableAreas([
                'before-wrapper' => 'pages.security-device-lots.partials.cards',
                'after-wrapper' => 'pages.security-device-lots.partials.table-footer',
            ]);
    }

    /**
     * Get every lot of the announcement for the cards shown on a phone instead of the table (H37, decided by the user on
     * 2026-11-24 for this screen).
     *
     * @return Collection<int, SecurityDeviceLot>
     */
    public function cardLots(): Collection
    {
        return $this->builder()->get();
    }

    /**
     * Check whether the lot can be removed: none of its devices changed status and the announcement keeps another lot.
     */
    public function canRemove(SecurityDeviceLot $lot): bool
    {
        $this->lotsCount ??= SecurityDeviceLot::query()->where('security_device_announcement_id', $this->announcementId)->count();

        return $this->lotsCount > 1 && $lot->seriesCanBeCorrected();
    }

    /**
     * Get the lots of the announcement in the order they were entered, with how many of their devices were approved (H29)
     * and how many changed status, which tells whether the lot can be removed.
     *
     * @return Builder<SecurityDeviceLot>
     */
    public function builder(): Builder
    {
        return SecurityDeviceLot::query()
            ->with('securityDeviceType')
            ->where('security_device_lots.security_device_announcement_id', $this->announcementId)
            ->withCount(['devices as approved_devices_count' => fn (Builder $devices) => $devices->whereNotIn('status', [SecurityDeviceStatus::Inactive->value, SecurityDeviceStatus::Discarded->value])])
            ->orderBy('security_device_lots.id');
    }

    /**
     * Get the columns of the table.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Tipo')
                ->label(fn (SecurityDeviceLot $row): string => $row->securityDeviceType->name),
            Column::make('Rango')
                ->label(fn (SecurityDeviceLot $row): string => $row->serialNumber($row->series_start).' – '.$row->serialNumber($row->series_end)),
            Column::make('Cantidad', 'quantity')
                ->format(fn (int $value): string => number_format($value, 0, ',', ' ')),
            Column::make('Aprobados')
                ->label(fn (SecurityDeviceLot $row): string => $row->requiresApproval()
                    ? sprintf(
                        '%s de %s',
                        number_format((int) $row->getAttribute('approved_devices_count'), 0, ',', ' '),
                        number_format($row->quantity, 0, ',', ' '),
                    )
                    : 'No requiere'),
            Column::make('Observaciones', 'notes')
                ->format(fn (?string $value): string => $value ?? '—'),
            Column::make('Acciones')
                ->label(fn (SecurityDeviceLot $row): View => view('pages.security-device-lots.partials.actions', [
                    'lot' => $row,
                    'canRemove' => $this->canRemove($row),
                ])),
        ];
    }

    /**
     * Remove a lot from its announcement, with its devices, when none of them changed status and the announcement keeps
     * another lot (H37, criterion 12).
     */
    public function remove(int $id, RemoveSecurityDeviceLot $removeLot): void
    {
        $lot = $this->builder()->findOrFail($id);
        $name = $lot->name();

        if (! $removeLot->handle($lot)) {
            $this->dispatch('toast', icon: 'warning', title: 'No se quitó el lote', text: 'Solo se quita un lote cuyos dispositivos siguen como se ingresaron, y el anuncio debe quedar con al menos un lote.');

            return;
        }

        session()->flash('toast', ['icon' => 'success', 'title' => 'Lote quitado', 'text' => sprintf('Se quitó el lote %s con sus dispositivos.', $name)]);

        $this->redirectRoute('intranet.security-device-announcements.show', $this->announcementId, navigate: true);
    }
}
