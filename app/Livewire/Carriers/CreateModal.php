<?php

namespace App\Livewire\Carriers;

use App\Livewire\Forms\CarrierForm;
use App\Services\SunatService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateModal extends Component
{
    public CarrierForm $form;

    /**
     * Look up the RUC and fill in the data the service finds.
     */
    public function lookup(SunatService $sunat, bool $force = false): void
    {
        $this->form->lookup($sunat, $force);
    }

    /**
     * Create the carrier and refresh the table.
     */
    public function save(): void
    {
        $this->form->store();
        $this->form->reset();

        $this->dispatch('toast', icon: 'success', title: 'Transportista creado', text: 'El transportista se creó correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Forget the validation errors and the lookup of a form that was closed without saving.
     */
    public function clearErrors(): void
    {
        $this->form->resetValidation();
        $this->form->reset('lookedUpDocument');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.carriers.create-modal');
    }
}
