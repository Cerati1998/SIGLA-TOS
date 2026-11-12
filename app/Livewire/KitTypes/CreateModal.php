<?php

namespace App\Livewire\KitTypes;

use App\Livewire\Forms\KitTypeForm;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateModal extends Component
{
    public KitTypeForm $form;

    /**
     * Prepare one entry per active security device type.
     */
    public function mount(): void
    {
        $this->form->fillDevices();
    }

    /**
     * Create the kit type and refresh the table.
     */
    public function save(): void
    {
        $this->form->store();
        $this->form->reset();
        $this->form->fillDevices();

        $this->dispatch('toast', icon: 'success', title: 'Tipo de kit creado', text: 'El tipo de kit ya se puede elegir al crear kits.');
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
     * Render the component with the container types and the security device types to choose from.
     */
    public function render(): View
    {
        return view('pages.kit-types.create-modal', [
            'containerTypes' => $this->form->containerTypeOptions(),
            'deviceTypes' => $this->form->deviceTypes(),
        ]);
    }
}
