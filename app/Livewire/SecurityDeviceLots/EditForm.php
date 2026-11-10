<?php

namespace App\Livewire\SecurityDeviceLots;

use App\Actions\SecurityDevices\CorrectSecurityDeviceLot;
use App\Concerns\OffersSecurityDeviceLotOrigins;
use App\Enums\SecurityDeviceLotCorrection;
use App\Enums\SecurityDeviceStatus;
use App\Livewire\Forms\SecurityDeviceLotForm;
use App\Models\Provider;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Livewire\Component;

/**
 * The correction of a lot (H28).
 *
 * @property-read Collection<int, ShippingLine> $shippingLines
 * @property-read Collection<int, Provider> $providers
 * @property-read Collection<int, SecurityDeviceType> $securityDeviceTypes
 */
class EditForm extends Component
{
    use OffersSecurityDeviceLotOrigins;

    public SecurityDeviceLot $lot;

    public SecurityDeviceLotForm $form;

    /**
     * Mount the component with the lot being corrected.
     */
    public function mount(SecurityDeviceLot $lot): void
    {
        $this->lot = $lot;
        $this->form->setLot($lot);
    }

    /**
     * Save the corrections and go back to the detail of the lot.
     */
    public function save(CorrectSecurityDeviceLot $correctLot): void
    {
        $correction = $this->form->update($correctLot, $this->lot);

        if ($correction === null) {
            return;
        }

        session()->flash('toast', [
            'icon' => 'success',
            'title' => 'Lote corregido',
            'text' => $correction === SecurityDeviceLotCorrection::Regenerated
                ? sprintf('Se rehicieron sus %s dispositivos, todos disponibles.', number_format($this->lot->quantity, 0, ',', ' '))
                : 'Se guardaron los cambios del lote.',
        ]);

        $this->redirectRoute('intranet.security-device-lots.show', $this->lot, navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.security-device-lots.lot-form', [
            'submitLabel' => 'Guardar cambios',
            'notSavedTitle' => 'No se guardaron los cambios.',
            'cancelUrl' => route('intranet.security-device-lots.show', $this->lot),
            'seriesLockedNotice' => $this->form->seriesLocked ? $this->seriesLockedNotice() : null,
        ]);
    }

    /**
     * Explain why the series cannot change, as in "20 dispositivos de este lote ya cambiaron de estado (15 en un kit y 5 usados)".
     */
    private function seriesLockedNotice(): string
    {
        $counts = Arr::except($this->lot->deviceCountsByStatus(), SecurityDeviceStatus::Available->value);
        $changed = array_sum($counts);

        $parts = collect($counts)
            ->filter()
            ->map(fn (int $count, string $status): string => $count.' '.mb_strtolower($count === 1
                ? SecurityDeviceStatus::from($status)->label()
                : SecurityDeviceStatus::from($status)->pluralLabel()))
            ->join(', ', ' y ');

        return $changed === 1
            ? "La serie no se puede cambiar porque 1 dispositivo de este lote ya cambió de estado ({$parts})."
            : "La serie no se puede cambiar porque {$changed} dispositivos de este lote ya cambiaron de estado ({$parts}).";
    }
}
