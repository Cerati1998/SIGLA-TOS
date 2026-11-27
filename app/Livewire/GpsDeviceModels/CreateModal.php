<?php

namespace App\Livewire\GpsDeviceModels;

use App\Livewire\Forms\GpsDeviceModelForm;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateModal extends Component
{
    public GpsDeviceModelForm $form;

    /**
     * Create the model, refresh the table and tell the page which model was created, so that a form that opened this
     * window (as the GPS device form, H40) can choose it.
     */
    public function save(): void
    {
        $gpsDeviceModel = $this->form->store();
        $this->form->reset();

        $this->dispatch('toast', icon: 'success', title: 'Modelo GPS guardado', text: 'El modelo GPS se creó correctamente.');
        $this->dispatch('refreshDatatable');
        $this->dispatch('gps-device-model-created', gpsDeviceModelId: $gpsDeviceModel->id);
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
