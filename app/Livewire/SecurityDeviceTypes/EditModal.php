<?php

namespace App\Livewire\SecurityDeviceTypes;

use App\Livewire\Forms\SecurityDeviceTypeForm;
use App\Models\SecurityDeviceType;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    public SecurityDeviceTypeForm $form;

    /**
     * Open the form filled with the given security device type.
     */
    #[On('edit-security-device-type')]
    public function edit(int $securityDeviceTypeId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setSecurityDeviceType(SecurityDeviceType::withTrashed()->findOrFail($securityDeviceTypeId));
        $this->form->openModal = true;
    }

    /**
     * Save the changes and refresh the table.
     */
    public function save(): void
    {
        $this->form->update(SecurityDeviceType::withTrashed()->findOrFail($this->form->securityDeviceTypeId));
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Tipo de dispositivo actualizado', text: 'El nombre del tipo de dispositivo se guardó correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.security-device-types.edit-modal');
    }
}
