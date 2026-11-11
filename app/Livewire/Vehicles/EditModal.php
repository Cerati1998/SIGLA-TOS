<?php

namespace App\Livewire\Vehicles;

use App\Enums\VehicleCategory;
use App\Enums\VehicleKind;
use App\Livewire\Forms\VehicleForm;
use App\Models\Vehicle;
use App\Models\VehicleBody;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    public VehicleForm $form;

    /**
     * Open the form filled with the given vehicle.
     */
    #[On('edit-vehicle')]
    public function edit(int $vehicleId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setVehicle(Vehicle::withTrashed()->findOrFail($vehicleId));
        $this->form->openModal = true;
    }

    /**
     * Save the changes and refresh the table.
     */
    public function save(): void
    {
        $this->form->update(Vehicle::withTrashed()->findOrFail($this->form->vehicleId));
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Vehículo actualizado', text: 'Los datos del vehículo se guardaron correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component with the active bodies, plus the one the vehicle has if it was deactivated.
     */
    public function render(): View
    {
        $bodies = VehicleBody::withTrashed()
            ->where(fn ($query) => $query->whereNull('deleted_at')->orWhere('id', $this->form->savedBodyId))
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (VehicleBody $body): array => [$body->id => $body->trashed() ? "{$body->name} (desactivada)" : $body->name]);

        return view('pages.vehicles.edit-modal', [
            'kinds' => VehicleKind::options(),
            'bodies' => $bodies,
            'categories' => VehicleCategory::options(),
        ]);
    }
}
