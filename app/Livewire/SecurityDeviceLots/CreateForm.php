<?php

namespace App\Livewire\SecurityDeviceLots;

use App\Actions\SecurityDevices\ReceiveSecurityDeviceLot;
use App\Livewire\Forms\SecurityDeviceLotForm;
use App\Models\Provider;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read Collection<int, ShippingLine> $shippingLines
 * @property-read Collection<int, Provider> $providers
 * @property-read Collection<int, SecurityDeviceType> $securityDeviceTypes
 */
class CreateForm extends Component
{
    public SecurityDeviceLotForm $form;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->form->setDefaults();
    }

    /**
     * Get the active shipping lines, by code.
     *
     * @return Collection<int, ShippingLine>
     */
    #[Computed]
    public function shippingLines(): Collection
    {
        return ShippingLine::query()->orderBy('code')->get(['id', 'code', 'name']);
    }

    /**
     * Get the active providers, by business name.
     *
     * @return Collection<int, Provider>
     */
    #[Computed]
    public function providers(): Collection
    {
        return Provider::query()->orderBy('business_name')->get(['id', 'business_name']);
    }

    /**
     * Get the active types of security device, by name (criterion 2).
     *
     * @return Collection<int, SecurityDeviceType>
     */
    #[Computed]
    public function securityDeviceTypes(): Collection
    {
        return SecurityDeviceType::query()->orderBy('name')->get(['id', 'name']);
    }

    /**
     * Save the lot and its devices, and leave the form ready for the next lot.
     */
    public function save(ReceiveSecurityDeviceLot $receiveLot): void
    {
        /** @var User $user */
        $user = Auth::user();

        $lot = $this->form->store($receiveLot, $user);

        if ($lot === null) {
            return;
        }

        $this->dispatch('toast', icon: 'success', title: 'Lote guardado', text: sprintf(
            'Se registraron %s dispositivos disponibles.',
            number_format($lot->quantity, 0, ',', ' '),
        ));
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.security-device-lots.create-form');
    }
}
