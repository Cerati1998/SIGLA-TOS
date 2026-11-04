<?php

namespace App\Livewire\SecurityDeviceTypes;

use App\Enums\SecurityDeviceKind;
use App\Livewire\Forms\SecurityDeviceTypeForm;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateModal extends Component
{
    public SecurityDeviceTypeForm $form;

    /**
     * Create the security device type and refresh the table.
     */
    public function save(): void
    {
        $this->form->store();
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Tipo de dispositivo creado', text: 'El tipo de dispositivo se creó correctamente.');
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
        return view('pages.security-device-types.create-modal', [
            'kinds' => SecurityDeviceKind::options(),
        ]);
    }
}
