<?php

namespace App\Livewire\YardZones;

use App\Livewire\Forms\YardZoneForm;
use App\Models\Branch;
use App\Models\YardZone;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    public YardZoneForm $form;

    /**
     * Open the form filled with the given zone.
     */
    #[On('edit-yard-zone')]
    public function edit(int $yardZoneId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setYardZone(YardZone::withTrashed()->findOrFail($yardZoneId));
        $this->form->openModal = true;
    }

    /**
     * Save the changes and refresh the table.
     */
    public function save(): void
    {
        $this->form->update(YardZone::withTrashed()->findOrFail($this->form->yardZoneId));
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Zona de patio guardada', text: 'Los datos de la zona de patio se guardaron correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component with the active branches, plus the one the zone has if it was deactivated.
     */
    public function render(): View
    {
        $branches = Branch::withTrashed()
            ->where(fn ($query) => $query->whereNull('deleted_at')->orWhere('id', $this->form->savedBranchId))
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Branch $branch): array => [$branch->id => $branch->trashed() ? "{$branch->name} (desactivada)" : $branch->name]);

        return view('pages.yard-zones.edit-modal', [
            'branches' => $branches,
        ]);
    }
}
