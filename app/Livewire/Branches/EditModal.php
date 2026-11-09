<?php

namespace App\Livewire\Branches;

use App\Actions\Session\StoreCompanyAndBranchInSession;
use App\Livewire\Forms\BranchForm;
use App\Models\Branch;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    public BranchForm $form;

    /**
     * Open the form filled with the given branch.
     */
    #[On('edit-branch')]
    public function edit(int $branchId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setBranch(Branch::withTrashed()->findOrFail($branchId));
        $this->form->openModal = true;
    }

    /**
     * Save the changes, refresh the table and the company and branch in the session of the signed-in user.
     */
    public function save(StoreCompanyAndBranchInSession $storeCompanyAndBranch): void
    {
        $this->form->update(Branch::withTrashed()->findOrFail($this->form->branchId));
        $storeCompanyAndBranch->forSignedInUser();
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Sucursal actualizada', text: 'Los datos de la sucursal se guardaron correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.branches.edit-modal');
    }
}
