<?php

namespace App\Livewire\Clients;

use App\Livewire\Forms\ClientForm;
use App\Models\Client;
use App\Services\SunatService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    public ClientForm $form;

    /**
     * Open the form filled with the given client.
     */
    #[On('edit-client')]
    public function edit(int $clientId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setClient(Client::withTrashed()->findOrFail($clientId));
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
        $this->form->update(Client::withTrashed()->findOrFail($this->form->clientId));
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Cliente actualizado', text: 'Los datos del cliente se guardaron correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.clients.edit-modal');
    }
}
