<?php

namespace App\Livewire\Certifications;

use App\Actions\Certifications\DescribeCertification;
use App\Models\Certification;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The detail of a certification, only to read (H20, criterion 4): the data kept from its request, the transport, the
 * kit, each inspection with its inspectors, start and end, and each section with its devices and photos. While it is
 * open it also leads to its two inspections (H18). Everything comes from SIGLA 2.0; SIGLA 1.0 is not looked up.
 */
class Details extends Component
{
    #[Locked]
    public Certification $certification;

    /**
     * Render the component.
     */
    public function render(DescribeCertification $describeCertification): View
    {
        return view('pages.certifications.details', $describeCertification->handle($this->certification));
    }
}
