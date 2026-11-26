<?php

namespace App\Livewire\Checkpoints;

use App\Actions\Reinspections\StartReinspection;
use App\Livewire\Forms\ReinspectionForm;
use App\Models\Checkpoint;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The window that starts a reinspection from the last step of a checkpoint with incorrect series (H35, criterion 1):
 * the sections that are reopened, with their incorrect series, and the required reason. It opens in the browser; the
 * server is called only to start it.
 */
class ReinspectionModal extends Component
{
    #[Locked]
    public Checkpoint $checkpoint;

    /**
     * The sections of the certification that are reopened, each with its type and its incorrect series, which the
     * step form already worked out.
     *
     * @var list<array{section: string, type: string, series: list<string>}>
     */
    #[Locked]
    public array $sections = [];

    public ReinspectionForm $form;

    /**
     * Start the reinspection and go back to the list of «Registrar checkpoint», where the unit no longer is.
     */
    public function start(StartReinspection $startReinspection): void
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $this->form->start($startReinspection, $this->checkpoint, $user);
        } catch (ValidationException $exception) {
            if (! $exception->validator->errors()->has('reinspection')) {
                throw $exception;
            }

            $this->form->openModal = false;
            $this->dispatch('toast', icon: 'warning', title: 'No se inició la reinspección', text: $exception->validator->errors()->first('reinspection'));

            return;
        }

        $this->dispatch('toast', icon: 'success', title: 'Reinspección iniciada', text: $this->checkpoint->certification->container.' pasó a reinspección: se reabren solo sus secciones con series incorrectas.');

        $this->redirectRoute('intranet.checkpoints.pending', navigate: true);
    }

    /**
     * Clear the errors of the window when it is closed with some.
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
        return view('pages.checkpoints.reinspection-modal');
    }
}
