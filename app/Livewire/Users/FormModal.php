<?php

namespace App\Livewire\Users;

use App\Livewire\Forms\UserForm;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class FormModal extends Component
{
    public UserForm $form;

    public bool $showPassword = false;

    #[Locked]
    public ?string $generatedPassword = null;

    /**
     * Open the empty form to create a user.
     */
    #[On('create-user')]
    public function create(): void
    {
        $this->closePassword();
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->openModal = true;
    }

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
     * Save the form and refresh the table.
     */
    public function save(): void
    {
        if ($this->form->userId === null) {
            $this->generatedPassword = $this->form->store();
            $this->showPassword = true;
        } else {
            $this->form->update(User::withTrashed()->findOrFail($this->form->userId));

            $this->dispatch('toast', icon: 'success', text: 'Usuario actualizado.');
        }

        $this->form->openModal = false;

        $this->dispatch('refreshDatatable');
    }

    /**
     * Close the password window and forget the password.
     */
    public function closePassword(): void
    {
        $this->showPassword = false;
        $this->generatedPassword = null;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.users.form-modal');
    }
}
