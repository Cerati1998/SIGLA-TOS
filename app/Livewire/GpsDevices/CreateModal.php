<?php

namespace App\Livewire\GpsDevices;

use App\Actions\GpsDevices\SaveGpsDevice;
use App\Livewire\Forms\GpsDeviceForm;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateModal extends Component
{
    use WithFileUploads;

    public GpsDeviceForm $form;

    /**
     * Register the device and refresh the table.
     */
    public function save(SaveGpsDevice $saveGpsDevice): void
    {
        $this->form->save($saveGpsDevice);
        $this->form->reset();

        $this->dispatch('toast', icon: 'success', title: 'Dispositivo GPS guardado', text: 'El dispositivo GPS se registró correctamente.');
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
        return view('pages.gps-devices.create-modal');
    }
}
