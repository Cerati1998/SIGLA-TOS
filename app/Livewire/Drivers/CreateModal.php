<?php

namespace App\Livewire\Drivers;

use App\Actions\Drivers\AddDriverToCarrier;
use App\Livewire\Forms\DriverForm;
use App\Models\Carrier;
use App\Services\SunatService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Add a driver to a carrier: a registered one is linked, a new one is created first.
 */
class CreateModal extends Component
{
    public DriverForm $form;

    /**
     * Fix the carrier the drivers are added to.
     */
    public function mount(int $carrierId): void
    {
        $this->form->carrierId = $carrierId;
    }

    /**
     * Look up the DNI and fill in the data that is found.
     */
    public function lookup(SunatService $sunat, bool $force = false): void
    {
        $this->form->lookup($sunat, $force);
    }

    /**
     * Add the driver to the carrier and refresh the tables.
     */
    public function save(AddDriverToCarrier $addDriverToCarrier): void
    {
        $this->form->storeForCarrier($addDriverToCarrier);
        $this->form->reset('openModal', 'registeredDriverId', 'documentNumber', 'fullName', 'license', 'lookupStatus', 'lookedUpDocument');

        $this->dispatch('toast', icon: 'success', title: 'Conductor agregado', text: 'El conductor se agregó al transportista.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Forget the validation errors and the lookup of a form that was closed without saving.
     */
    public function clearErrors(): void
    {
        $this->form->resetValidation();
        $this->form->reset('lookedUpDocument', 'registeredDriverId');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.drivers.create-modal', [
            'carrierName' => Carrier::withTrashed()->whereKey($this->form->carrierId)->value('business_name'),
        ]);
    }
}
