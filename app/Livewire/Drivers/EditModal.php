<?php

namespace App\Livewire\Drivers;

use App\Livewire\Forms\DriverForm;
use App\Models\Driver;
use App\Services\SunatService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    public DriverForm $form;

    /**
     * Open the form filled with the given driver.
     */
    #[On('edit-driver')]
    public function edit(int $driverId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setDriver(Driver::withTrashed()->findOrFail($driverId));
        $this->form->openModal = true;
    }

    /**
     * Look up the DNI and fill in the data the service finds.
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
        $this->form->update(Driver::withTrashed()->findOrFail($this->form->driverId));
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Conductor actualizado', text: 'Los datos del conductor se guardaron correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.drivers.edit-modal');
    }
}
