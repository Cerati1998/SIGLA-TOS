<?php

namespace App\Livewire\CertificationSections;

use App\Models\CertificationSection;
use App\Services\CertificationSectionOrder;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Changes the order of the active sections: dragging on a computer, with up and down buttons on a phone.
 * Each move is saved at once.
 */
class OrderModal extends Component
{
    public bool $openModal = false;

    /**
     * Move the dragged section to its new position, counted from zero.
     */
    public function sort(int $certificationSectionId, int $position, CertificationSectionOrder $order): void
    {
        $order->moveTo(CertificationSection::query()->findOrFail($certificationSectionId), $position);

        $this->dispatch('refreshDatatable');
    }

    /**
     * Move the section one place up or down.
     */
    public function move(int $certificationSectionId, int $offset, CertificationSectionOrder $order): void
    {
        $section = CertificationSection::query()->findOrFail($certificationSectionId);

        $order->moveTo($section, (int) $section->sort_order - 1 + ($offset < 0 ? -1 : 1));

        $this->dispatch('refreshDatatable');
    }

    /**
     * Render the component.
     */
    public function render(CertificationSectionOrder $order): View
    {
        return view('pages.certification-sections.order-modal', [
            'sections' => $order->activeSections(),
        ]);
    }
}
