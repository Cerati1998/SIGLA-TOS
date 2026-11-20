<?php

namespace App\Livewire\Certifications;

use App\Actions\Certifications\ReopenCertification;
use App\Livewire\Forms\CertificationReopeningForm;
use App\Models\Certification;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * The window that asks for the reason to reopen a closed certification, from the list of assignments (H19,
 * criterion 7).
 */
class ReopenModal extends Component
{
    public CertificationReopeningForm $form;

    /**
     * Open the window for the given certification, if it is closed.
     */
    #[On('reopen-certification')]
    public function open(int $certificationId): void
    {
        $certification = Certification::query()->findOrFail($certificationId);

        if (! $certification->isClosed()) {
            $this->dispatch('toast', icon: 'warning', title: 'No está cerrada', text: 'Esta certificación ya se puede corregir.');
            $this->dispatch('refreshDatatable');

            return;
        }

        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setCertification($certification);
        $this->form->openModal = true;
    }

    /**
     * Reopen the certification, close the window and refresh the list.
     */
    public function reopen(ReopenCertification $reopenCertification): void
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $this->form->reopen($reopenCertification, $user);
        } catch (ValidationException $exception) {
            if (! $exception->validator->errors()->has('certification')) {
                throw $exception;
            }

            $this->form->openModal = false;
            $this->dispatch('toast', icon: 'warning', title: 'No se puede reaperturar', text: $exception->validator->errors()->first('certification'));
            $this->dispatch('refreshDatatable');

            return;
        }

        $this->form->openModal = false;
        $this->dispatch('toast', icon: 'success', title: 'Certificación reaperturada', text: "Ya puedes corregir las fotos de {$this->form->container} y cerrarla otra vez.");
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.certifications.reopen-modal');
    }
}
