<?php

namespace App\Livewire\Users;

use App\Livewire\Forms\UserForm;
use App\Services\SunatService;
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
     * Look up the DNI and fill in the name the service finds.
     */
    public function lookup(SunatService $sunat, bool $force = false): void
    {
        $this->form->lookup($sunat, $force);
    }

    /**
     * Create the user, show the generated password and refresh the table.
     * The name and the email stay in the form for the password window.
     */
    public function save(): void
    {
        $this->generatedPassword = $this->form->store();
        $this->showPassword = true;
        $this->form->openModal = false;
        $this->form->reset('lookupStatus', 'lookedUpDocument');

        $this->dispatch('refreshDatatable');
    }

    /**
     * Forget the validation errors and the lookup of a form that was closed without saving.
     */
    public function clearErrors(): void
    {
        $this->form->resetValidation();
        $this->form->reset('lookedUpDocument');
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
