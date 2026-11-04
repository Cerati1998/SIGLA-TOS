<?php

namespace App\Livewire\Branches;

use App\Livewire\Forms\BranchForm;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateModal extends Component
{
    public BranchForm $form;

    /**
     * Create the branch and refresh the table.
     */
    public function save(): void
    {
        $branch = $this->form->store();
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Sucursal creada', text: "La sucursal se creó con el código {$branch->code}.");
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
        return view('pages.branches.create-modal');
    }
}
