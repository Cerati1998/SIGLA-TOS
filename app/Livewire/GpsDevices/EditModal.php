<?php

namespace App\Livewire\GpsDevices;

use App\Actions\GpsDevices\SaveGpsDevice;
use App\Livewire\Forms\GpsDeviceForm;
use App\Models\GpsDevice;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditModal extends Component
{
    use WithFileUploads;

    public GpsDeviceForm $form;

    /**
     * Open the form filled with the given device.
     */
    #[On('edit-gps-device')]
    public function edit(int $gpsDeviceId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setGpsDevice(GpsDevice::withTrashed()->findOrFail($gpsDeviceId));
        $this->form->openModal = true;
    }

    /**
     * Save the changes and refresh the table.
     */
    public function save(SaveGpsDevice $saveGpsDevice): void
    {
        $this->form->save($saveGpsDevice);
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Dispositivo GPS guardado', text: 'Los datos del dispositivo GPS se guardaron correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.gps-devices.edit-modal');
    }
}
