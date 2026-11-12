<?php

namespace App\Livewire\KitTypes;

use App\Livewire\Forms\KitTypeForm;
use App\Models\KitType;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    public KitTypeForm $form;

    /**
     * Open the form filled with the given kit type.
     */
    #[On('edit-kit-type')]
    public function edit(int $kitTypeId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setKitType(KitType::withTrashed()->with('securityDeviceTypes')->findOrFail($kitTypeId));
        $this->form->openModal = true;
    }

    /**
     * Save the changes and refresh the table.
     */
    public function save(): void
    {
        $this->form->update(KitType::withTrashed()->findOrFail($this->form->kitTypeId));
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Tipo de kit actualizado', text: 'Los kits ya armados no cambian.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component with the container types and the security device types to choose from, plus the ones the
     * kit type already has if they were deactivated.
     */
    public function render(): View
    {
        return view('pages.kit-types.edit-modal', [
            'containerTypes' => $this->form->containerTypeOptions(),
            'deviceTypes' => $this->form->deviceTypes(),
        ]);
    }
}
