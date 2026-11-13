<?php

namespace App\Livewire\Providers;

use App\Livewire\Forms\ProviderForm;
use App\Services\SunatService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateModal extends Component
{
    public ProviderForm $form;

    /**
     * Look up the document and fill in the data the service finds.
     */
    public function lookup(SunatService $sunat, bool $force = false): void
    {
        $this->form->lookup($sunat, $force);
    }

    /**
     * Create the provider, refresh the table and tell the page which provider was created, so that a form that opened
     * this window (as the worker form) can choose it.
     */
    public function save(): void
    {
        $provider = $this->form->store();
        $this->form->reset();

        $this->dispatch('toast', icon: 'success', title: 'Proveedor creado', text: 'El proveedor se creó correctamente.');
        $this->dispatch('refreshDatatable');
        $this->dispatch('provider-created', providerId: $provider->id);
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
        return view('pages.providers.create-modal');
    }
}
