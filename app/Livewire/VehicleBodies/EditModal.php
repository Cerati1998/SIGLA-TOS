<?php

namespace App\Livewire\VehicleBodies;

use App\Livewire\Forms\VehicleBodyForm;
use App\Models\VehicleBody;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    public VehicleBodyForm $form;

    /**
     * Open the form filled with the given body.
     */
    #[On('edit-vehicle-body')]
    public function edit(int $vehicleBodyId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setVehicleBody(VehicleBody::withTrashed()->findOrFail($vehicleBodyId));
        $this->form->openModal = true;
    }

    /**
     * Save the changes and refresh the table.
     */
    public function save(): void
    {
        $this->form->update(VehicleBody::withTrashed()->findOrFail($this->form->vehicleBodyId));
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Carrocería actualizada', text: 'La carrocería se guardó correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.vehicle-bodies.edit-modal');
    }
}
