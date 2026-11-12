<?php

namespace App\Livewire\Carriers;

use App\Models\Carrier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * The drivers of one carrier, with the way to add more.
 */
class DriversModal extends Component
{
    public bool $openModal = false;

    #[Locked]
    public ?int $carrierId = null;

    /**
     * Open the drivers of the given carrier.
     */
    #[On('show-carrier-drivers')]
    public function open(int $carrierId): void
    {
        $this->carrierId = Carrier::withTrashed()->findOrFail($carrierId)->id;
        $this->openModal = true;
    }

    /**
     * Render the component with the carrier shown.
     */
    public function render(): View
    {
        return view('pages.carriers.drivers-modal', [
            'carrier' => $this->carrierId === null ? null : Carrier::withTrashed()->find($this->carrierId),
        ]);
    }
}
