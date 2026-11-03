<?php

namespace App\Livewire\Users;

use App\Livewire\Forms\UserForm;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CreateModal extends Component
{
    public UserForm $form;

    public bool $showPassword = false;

    #[Locked]
    public ?string $generatedPassword = null;

    /**
     * Create the user, show the generated password and refresh the table.
     */
    public function save(): void
    {
        $this->generatedPassword = $this->form->store();
        $this->showPassword = true;
        $this->form->openModal = false;

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
        return view('pages.users.create-modal');
    }
}
