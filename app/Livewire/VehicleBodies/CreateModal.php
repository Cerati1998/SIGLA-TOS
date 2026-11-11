<?php

namespace App\Livewire\VehicleBodies;

use App\Livewire\Forms\VehicleBodyForm;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateModal extends Component
{
    public VehicleBodyForm $form;

    /**
     * Create the body and refresh the table.
     */
    public function save(): void
    {
        $this->form->store();
        $this->form->reset();

        $this->dispatch('toast', icon: 'success', title: 'Carrocería creada', text: 'La carrocería se creó correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Forget the validation errors of a form that was closed without saving.
     */
    public function clearErrors(): void
    {
        $this->form->resetValidation();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.vehicle-bodies.create-modal');
    }
}
