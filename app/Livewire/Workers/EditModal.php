<?php

namespace App\Livewire\Workers;

use App\Livewire\Forms\WorkerForm;
use App\Models\Worker;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditModal extends Component
{
    public WorkerForm $form;

    /**
     * Open the form filled with the given worker.
     */
    #[On('edit-worker')]
    public function edit(int $workerId): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setWorker(Worker::withTrashed()->findOrFail($workerId));
        $this->form->openModal = true;
    }

    /**
     * Save the changes and refresh the table.
     */
    public function save(): void
    {
        $this->form->update(Worker::withTrashed()->findOrFail($this->form->workerId));
        $this->form->openModal = false;

        $this->dispatch('toast', icon: 'success', title: 'Trabajador actualizado', text: 'Los datos del trabajador se guardaron correctamente.');
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.workers.edit-modal');
    }
}
