<?php

namespace App\Livewire\CertificationSections;

use App\Enums\CertificationSectionType;
use App\Livewire\Forms\CertificationSectionForm;
use App\Services\CertificationSectionOrder;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateModal extends Component
{
    public CertificationSectionForm $form;

    /**
     * Create the section at the end of the order and refresh the table.
     */
    public function save(CertificationSectionOrder $order): void
    {
        $this->form->store($order);
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Sección creada', text: 'La sección se creó al final del orden.');
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
        return view('pages.certification-sections.create-modal', [
            'types' => CertificationSectionType::options(),
        ]);
    }
}
