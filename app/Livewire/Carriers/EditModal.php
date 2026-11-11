<?php

namespace App\Livewire\Carriers;

use App\Livewire\Forms\CarrierForm;
use App\Models\Carrier;
use App\Services\SunatService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    public CarrierForm $form;

    /**
     * Open the form filled with the given carrier.
     */
    #[On('edit-carrier')]
    public function edit(int $carrierId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setCarrier(Carrier::withTrashed()->findOrFail($carrierId));
        $this->form->openModal = true;
    }

    /**
     * Look up the RUC and fill in the data the service finds.
     */
    public function lookup(SunatService $sunat, bool $force = false): void
    {
        $this->form->lookup($sunat, $force);
    }

    /**
     * Save the changes and refresh the table.
     */
    public function save(): void
    {
        $this->form->update(Carrier::withTrashed()->findOrFail($this->form->carrierId));
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Transportista actualizado', text: 'Los datos del transportista se guardaron correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.carriers.edit-modal');
    }
}
