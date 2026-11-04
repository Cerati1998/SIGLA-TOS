<?php

namespace App\Livewire\ShippingLines;

use App\Livewire\Forms\ShippingLineForm;
use App\Models\ShippingLine;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    public ShippingLineForm $form;

    /**
     * Open the form filled with the given shipping line.
     */
    #[On('edit-shipping-line')]
    public function edit(int $shippingLineId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setShippingLine(ShippingLine::withTrashed()->findOrFail($shippingLineId));
        $this->form->openModal = true;
    }

    /**
     * Save the changes and refresh the table.
     */
    public function save(): void
    {
        $this->form->update(ShippingLine::withTrashed()->findOrFail($this->form->shippingLineId));
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Línea naviera actualizada', text: 'Los datos de la línea naviera se guardaron correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.shipping-lines.edit-modal');
    }
}
