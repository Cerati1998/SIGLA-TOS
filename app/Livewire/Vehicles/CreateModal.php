<?php

namespace App\Livewire\Vehicles;

use App\Enums\VehicleCategory;
use App\Enums\VehicleKind;
use App\Livewire\Forms\VehicleForm;
use App\Models\VehicleBody;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateModal extends Component
{
    public VehicleForm $form;

    /**
     * Create the vehicle and refresh the table.
     */
    public function save(): void
    {
        $this->form->store();
        $this->form->reset();

        $this->dispatch('toast', icon: 'success', title: 'Vehículo creado', text: 'El vehículo se creó correctamente.');
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
     * Render the component with the active bodies to choose from.
     */
    public function render(): View
    {
        return view('pages.vehicles.create-modal', [
            'kinds' => VehicleKind::options(),
            'bodies' => VehicleBody::query()->orderBy('name')->pluck('name', 'id'),
            'categories' => VehicleCategory::options(),
        ]);
    }
}
