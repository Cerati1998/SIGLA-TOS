<?php

namespace App\Livewire\Certifications;

use App\Models\Certification;
use App\Services\Sigla1LookupResult;
use App\Services\Sigla1LookupStatus;
use App\Services\Sigla1Service;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * A certification already started (H17). What it keeps is shown as it is; the client, the product and the data of
 * the driver are looked up again in SIGLA 1.0, because the certification never copies them.
 *
 * @phpstan-import-type GateOutRequest from Sigla1LookupResult
 */
class Details extends Component
{
    #[Locked]
    public Certification $certification;

    /**
     * How the lookup in SIGLA 1.0 ended.
     */
    #[Locked]
    public ?Sigla1LookupStatus $status = null;

    /**
     * The request of the certification as SIGLA 1.0 sends it now.
     *
     * @var GateOutRequest|null
     */
    #[Locked]
    public ?array $gateOutRequest = null;

    /**
     * Load the certification and look up its request.
     */
    public function mount(Certification $certification, Sigla1Service $sigla1): void
    {
        $this->certification = $certification;

        $this->lookUp($sigla1);
    }

    /**
     * Look up the request in SIGLA 1.0 with the booking and the container that the certification keeps, and take the
     * one with its reference.
     */
    public function lookUp(Sigla1Service $sigla1): void
    {
        $result = $sigla1->findGateOutRequests($this->certification->booking, $this->certification->container);
        $gateOutRequest = collect($result->gateOutRequests)
            ->firstWhere('reference', $this->certification->gate_out_request_reference);

        $this->status = $result->status === Sigla1LookupStatus::Found && $gateOutRequest === null
            ? Sigla1LookupStatus::NotFound
            : $result->status;
        $this->gateOutRequest = $gateOutRequest;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $this->certification->loadMissing(['shippingLine', 'carrier', 'driver', 'tractor', 'semiTrailer', 'kit.kitType', 'kit.devices', 'yardZone', 'certifier']);

        return view('pages.certifications.details');
    }
}
