<?php

namespace App\Livewire\SecurityDeviceLots;

use App\Actions\SecurityDevices\ReceiveSecurityDeviceLot;
use App\Concerns\OffersSecurityDeviceLotOrigins;
use App\Livewire\Forms\SecurityDeviceLotForm;
use App\Models\Provider;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * @property-read Collection<int, ShippingLine> $shippingLines
 * @property-read Collection<int, Provider> $providers
 * @property-read Collection<int, SecurityDeviceType> $securityDeviceTypes
 */
class CreateForm extends Component
{
    use OffersSecurityDeviceLotOrigins;

    public SecurityDeviceLotForm $form;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->form->setDefaults();
    }

    /**
     * Save the lot and its devices, and open its detail.
     */
    public function save(ReceiveSecurityDeviceLot $receiveLot): void
    {
        /** @var User $user */
        $user = Auth::user();

        $lot = $this->form->store($receiveLot, $user);

        if ($lot === null) {
            return;
        }

        session()->flash('toast', [
            'icon' => 'success',
            'title' => 'Lote guardado',
            'text' => sprintf('Se registraron %s dispositivos disponibles.', number_format($lot->quantity, 0, ',', ' ')),
        ]);

        $this->redirectRoute('intranet.security-device-lots.show', $lot, navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.security-device-lots.lot-form', [
            'submitLabel' => 'Guardar lote',
            'notSavedTitle' => 'No se guardó el lote.',
            'cancelUrl' => route('intranet.security-device-lots.index'),
            'seriesLockedNotice' => null,
        ]);
    }
}
