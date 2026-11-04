<?php

namespace App\Livewire\Company;

use App\Livewire\Forms\CompanyForm;
use App\Models\Company;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * @property-read Company $company
 */
class EditForm extends Component
{
    use WithFileUploads;

    public CompanyForm $form;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->form->setCompany($this->company);
    }

    /**
     * Get the only company of the system.
     */
    #[Computed]
    public function company(): Company
    {
        return Company::query()->firstOrFail();
    }

    /**
     * Save the changes to the company.
     */
    public function save(): void
    {
        $this->form->update($this->company);

        $this->dispatch('toast', icon: 'success', title: 'Empresa actualizada', text: 'Los datos de la empresa se guardaron correctamente.');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.company.edit-form');
    }
}
