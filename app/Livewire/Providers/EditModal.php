<?php

namespace App\Livewire\Providers;

use App\Livewire\Forms\ProviderForm;
use App\Models\Provider;
use App\Services\SunatService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    public ProviderForm $form;

    /**
     * Open the form filled with the given provider.
     */
    #[On('edit-provider')]
    public function edit(int $providerId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setProvider(Provider::withTrashed()->findOrFail($providerId));
        $this->form->openModal = true;
    }

    /**
     * Look up the document and fill in the data the service finds.
     */
    public function lookup(SunatService $sunat, bool $force = false): void
    {
        $this->form->lookup($sunat, $force);
    }

    /**
     * Save the changes and refresh the table.
     */
    public function save(): void
    {
        $this->form->update(Provider::withTrashed()->findOrFail($this->form->providerId));
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Proveedor actualizado', text: 'Los datos del proveedor se guardaron correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.providers.edit-modal');
    }
}
