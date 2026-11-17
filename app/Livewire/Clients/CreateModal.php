<?php

namespace App\Livewire\Clients;

use App\Livewire\Forms\ClientForm;
use App\Services\SunatService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateModal extends Component
{
    public ClientForm $form;

    /**
     * Look up the document and fill in the data the service finds.
     */
    public function lookup(SunatService $sunat, bool $force = false): void
    {
        $this->form->lookup($sunat, $force);
    }

    /**
     * Create the client, refresh the table and tell the page which client was created, so that a form that opened
     * this window can choose it.
     */
    public function save(): void
    {
        $client = $this->form->store();
        $this->form->reset();

        $this->dispatch('toast', icon: 'success', title: 'Cliente creado', text: 'El cliente se creó correctamente.');
        $this->dispatch('refreshDatatable');
        $this->dispatch('client-created', clientId: $client->id);
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
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.clients.create-modal');
    }
}
