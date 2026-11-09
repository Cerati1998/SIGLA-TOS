<?php

namespace App\Livewire\Users;

use App\Actions\Session\StoreCompanyAndBranchInSession;
use App\Livewire\Forms\UserForm;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    public UserForm $form;

    /**
     * Open the form filled with the given user.
     */
    #[On('edit-user')]
    public function edit(int $userId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setUser(User::withTrashed()->findOrFail($userId));
        $this->form->openModal = true;
    }

    /**
     * Save the changes, refresh the table and the company and branch in the session of the signed-in user.
     */
    public function save(StoreCompanyAndBranchInSession $storeCompanyAndBranch): void
    {
        $this->form->update(User::withTrashed()->findOrFail($this->form->userId));
        $storeCompanyAndBranch->forSignedInUser();
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Usuario actualizado', text: 'Los datos del usuario se guardaron correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.users.edit-modal');
    }
}
