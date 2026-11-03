<?php

namespace App\Livewire\Users;

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
     * Save the changes and refresh the table.
     */
    public function save(): void
    {
        $this->form->update(User::withTrashed()->findOrFail($this->form->userId));
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', text: 'Usuario actualizado.');
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
