<?php

namespace App\Livewire\Inspections;

use App\Actions\Certifications\ListPendingInspections;
use App\Enums\CertificationSectionType;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The assigned units whose internal or external inspection is pending, to choose one (H18, criterion 2). The search
 * by container or pouch filters the list already loaded, in the browser.
 */
class UnitPicker extends Component
{
    #[Locked]
    public CertificationSectionType $type;

    /**
     * Render the component.
     */
    public function render(ListPendingInspections $listPendingInspections): View
    {
        return view('pages.inspections.unit-picker', [
            'units' => $listPendingInspections->handle($this->type),
        ]);
    }
}
