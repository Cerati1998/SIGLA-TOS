<?php

namespace App\Livewire\GpsDeviceModels;

use App\Livewire\Forms\GpsDeviceModelForm;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateModal extends Component
{
    public GpsDeviceModelForm $form;

    /**
     * Create the model and refresh the table.
     */
    public function save(): void
    {
        $this->form->store();
        $this->form->reset();

        $this->dispatch('toast', icon: 'success', title: 'Modelo GPS guardado', text: 'El modelo GPS se creó correctamente.');
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
        return view('pages.gps-device-models.create-modal');
    }
}
