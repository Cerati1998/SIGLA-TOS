<?php

namespace App\Livewire\GpsDeviceModels;

use App\Livewire\Forms\GpsDeviceModelForm;
use App\Models\GpsDeviceModel;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    public GpsDeviceModelForm $form;

    /**
     * Open the form filled with the given model.
     */
    #[On('edit-gps-device-model')]
    public function edit(int $gpsDeviceModelId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setGpsDeviceModel(GpsDeviceModel::withTrashed()->findOrFail($gpsDeviceModelId));
        $this->form->openModal = true;
    }

    /**
     * Save the changes and refresh the table.
     */
    public function save(): void
    {
        $this->form->update(GpsDeviceModel::withTrashed()->findOrFail($this->form->gpsDeviceModelId));
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Modelo GPS guardado', text: 'Los datos del modelo GPS se guardaron correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.gps-device-models.edit-modal');
    }
}
