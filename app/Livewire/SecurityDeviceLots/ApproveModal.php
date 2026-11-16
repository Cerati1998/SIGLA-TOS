<?php

namespace App\Livewire\SecurityDeviceLots;

use App\Actions\SecurityDevices\ApproveSecurityDeviceLotRange;
use App\Livewire\Forms\SecurityDeviceLotApprovalForm;
use App\Models\SecurityDeviceLot;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Register the approval of a stretch of a lot, once its shipping line confirmed it (H29).
 */
class ApproveModal extends Component
{
    public SecurityDeviceLotApprovalForm $form;

    /**
     * Fix the lot whose stretches are approved.
     */
    public function mount(SecurityDeviceLot $lot): void
    {
        $this->form->setLot($lot);
    }

    /**
     * Check the stretch as soon as both correlatives are written, so the preview only shows a valid one.
     */
    public function updated(string $property): void
    {
        if (! in_array($property, ['form.correlativeStart', 'form.correlativeEnd'], true)) {
            return;
        }

        $this->form->resetValidation();

        if ($this->form->isComplete()) {
            $this->form->validate();
        }
    }

    /**
     * Register the approval and reload the detail of the lot, with its counts, devices and approvals log.
     */
    public function save(ApproveSecurityDeviceLotRange $approveRange): void
    {
        /** @var User $user */
        $user = auth()->user();
        $approval = $this->form->store($approveRange, $user);

        if ($approval === null) {
            $this->dispatch('toast', icon: 'warning', title: 'Nada que aprobar', text: 'Todos los dispositivos del tramo ya estaban aprobados.');

            return;
        }

        session()->flash('toast', [
            'icon' => 'success',
            'title' => 'Aprobación registrada',
            'text' => sprintf(
                $approval->approved_count === 1 ? '%s dispositivo pasó a Disponible.' : '%s dispositivos pasaron a Disponible.',
                number_format($approval->approved_count, 0, ',', ' '),
            ),
        ]);

        $this->redirectRoute('intranet.security-device-lots.show', $this->form->lotId, navigate: true);
    }

    /**
     * Forget the validation errors of a form that was closed without saving.
     */
    public function clearErrors(): void
    {
        $this->form->resetValidation();
    }

    /**
     * Render the component with the preview of a complete and valid stretch.
     */
    public function render(): View
    {
        $preview = $this->form->isComplete() && $this->getErrorBag()->isEmpty()
            ? app(ApproveSecurityDeviceLotRange::class)->preview($this->form->lot(), (int) $this->form->correlativeStart, (int) $this->form->correlativeEnd)
            : null;

        return view('pages.security-device-lots.approve-modal', [
            'preview' => $preview,
        ]);
    }
}
