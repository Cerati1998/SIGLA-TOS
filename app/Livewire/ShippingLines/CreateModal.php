<?php

namespace App\Livewire\ShippingLines;

use App\Livewire\Forms\ShippingLineForm;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateModal extends Component
{
    public ShippingLineForm $form;

    /**
     * Create the shipping line and refresh the table.
     */
    public function save(): void
    {
        $this->form->store();
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Línea naviera creada', text: 'La línea naviera se creó correctamente.');
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
        return view('pages.shipping-lines.create-modal');
    }
}
