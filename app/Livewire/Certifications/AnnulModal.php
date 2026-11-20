<?php

namespace App\Livewire\Certifications;

use App\Actions\Certifications\AnnulCertification;
use App\Enums\CertificationKitDecision;
use App\Livewire\Forms\CertificationAnnulmentForm;
use App\Models\Certification;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * The window that annuls the assignment of the kit of a certification, from the list of assignments (H32).
 */
class AnnulModal extends Component
{
    public CertificationAnnulmentForm $form;

    /**
     * Open the window for the given certification, if it can still be annulled.
     */
    #[On('annul-certification')]
    public function open(int $certificationId): void
    {
        $certification = Certification::query()->with('kit')->findOrFail($certificationId);

        if ($certification->isReadOnly()) {
            $this->dispatch('toast', icon: 'warning', title: 'No se puede anular', text: $certification->isAnnulled() ? 'Su asignación ya estaba anulada.' : 'Está cerrada: una certificación cerrada no se anula.');
            $this->dispatch('refreshDatatable');

            return;
        }

        $this->form->reset();
        $this->form->resetValidation();
        $this->form->setCertification($certification);
        $this->form->openModal = true;
    }

    /**
     * Annul the assignment, close the window and refresh the list.
     */
    public function annul(AnnulCertification $annulCertification): void
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $this->form->annul($annulCertification, $user);
        } catch (ValidationException $exception) {
            if (! $exception->validator->errors()->has('certification')) {
                throw $exception;
            }

            $this->form->openModal = false;
            $this->dispatch('toast', icon: 'warning', title: 'No se puede anular', text: $exception->validator->errors()->first('certification'));
            $this->dispatch('refreshDatatable');

            return;
        }

        $this->form->openModal = false;
        $this->dispatch('toast', icon: 'success', title: 'Asignación anulada', text: CertificationKitDecision::from($this->form->kitDecision) === CertificationKitDecision::Reuse
            ? "Se anuló la asignación de {$this->form->container}. El kit {$this->form->pouchCode} volvió a Despachado y se puede asignar otra vez."
            : "Se anuló la asignación de {$this->form->container}. El kit {$this->form->pouchCode} quedó Inhabilitado y sus dispositivos, descartados.");
        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.certifications.annul-modal', [
            'decisions' => CertificationKitDecision::cases(),
        ]);
    }
}
