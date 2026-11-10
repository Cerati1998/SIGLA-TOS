<?php

namespace App\Livewire\GateOutRequests;

use App\Services\Sigla1LookupResult;
use App\Services\Sigla1LookupStatus;
use App\Services\Sigla1Service;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Live lookup of a gate out request in SIGLA 1.0 (H05). It only shows the data: nothing is saved.
 *
 * @phpstan-import-type GateOutRequest from Sigla1LookupResult
 */
class Search extends Component
{
    public string $booking = '';

    public string $container = '';

    /**
     * How the last lookup ended, or null before the first one.
     */
    #[Locked]
    public ?Sigla1LookupStatus $status = null;

    /**
     * The requests found by the last lookup.
     *
     * @var list<GateOutRequest>
     */
    #[Locked]
    public array $gateOutRequests = [];

    /**
     * Look up the request in SIGLA 1.0. What was written stays, so a failed lookup can be retried as it is.
     */
    public function search(Sigla1Service $sigla1): void
    {
        $this->reset('status', 'gateOutRequests');

        $this->booking = mb_strtoupper(trim($this->booking));
        $this->container = mb_strtoupper(trim($this->container));

        $this->validate([
            'booking' => ['required', 'string', 'max:50'],
            'container' => ['required', 'string', 'max:50'],
        ], [
            'booking.required' => 'Escribe el booking.',
            'container.required' => 'Escribe el contenedor.',
        ], [
            'booking' => 'booking',
            'container' => 'contenedor',
        ]);

        $result = $sigla1->findGateOutRequests($this->booking, $this->container);

        $this->status = $result->status;
        $this->gateOutRequests = $result->gateOutRequests;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.gate-out-requests.search');
    }
}
