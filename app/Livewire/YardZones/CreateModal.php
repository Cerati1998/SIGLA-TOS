<?php

namespace App\Livewire\YardZones;

use App\Livewire\Forms\YardZoneForm;
use App\Models\Branch;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateModal extends Component
{
    public YardZoneForm $form;

    /**
     * Create the zone and refresh the table.
     */
    public function save(): void
    {
        $this->form->store();
        $this->form->reset();

        $this->dispatch('toast', icon: 'success', title: 'Zona de patio guardada', text: 'La zona de patio se creó correctamente.');
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
     * Render the component with the active branches to choose from.
     */
    public function render(): View
    {
        return view('pages.yard-zones.create-modal', [
            'branches' => Branch::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
