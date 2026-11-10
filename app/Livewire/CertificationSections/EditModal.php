<?php

namespace App\Livewire\CertificationSections;

use App\Enums\CertificationSectionType;
use App\Livewire\Forms\CertificationSectionForm;
use App\Models\CertificationSection;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    public CertificationSectionForm $form;

    /**
     * Open the form filled with the given section.
     */
    #[On('edit-certification-section')]
    public function edit(int $certificationSectionId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setCertificationSection(CertificationSection::withTrashed()->findOrFail($certificationSectionId));
        $this->form->openModal = true;
    }

    /**
     * Save the changes and refresh the table.
     */
    public function save(): void
    {
        $this->form->update(CertificationSection::withTrashed()->findOrFail($this->form->certificationSectionId));
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Sección actualizada', text: 'Los cambios de la sección se guardaron correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.certification-sections.edit-modal', [
            'types' => CertificationSectionType::options(),
        ]);
    }
}
