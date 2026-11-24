<?php

namespace App\Livewire\SecurityDeviceAnnouncements;

use App\Actions\SecurityDevices\ReceiveSecurityDeviceLots;
use App\Concerns\OffersSecurityDeviceLotOrigins;
use App\Livewire\Forms\SecurityDeviceAnnouncementForm;
use App\Models\Provider;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The entry of a new announcement of the entry of lots with its lots, or of new lots in an announcement already saved
 * (H37). The lots are listed in the browser and saved together.
 *
 * @property-read Collection<int, ShippingLine> $shippingLines
 * @property-read Collection<int, Provider> $providers
 * @property-read Collection<int, SecurityDeviceType> $securityDeviceTypes
 * @property-read array<int, array{usesSeries: bool, requiresApproval: bool}> $securityDeviceTypeBoxes
 */
class CreateForm extends Component
{
    use OffersSecurityDeviceLotOrigins;

    /**
     * The announcement that gets new lots, or null for a new announcement.
     */
    #[Locked]
    public ?SecurityDeviceAnnouncement $announcement = null;

    public SecurityDeviceAnnouncementForm $form;

    /**
     * Mount the component, with the announcement when lots are added to it.
     */
    public function mount(?SecurityDeviceAnnouncement $announcement = null): void
    {
        $this->announcement = $announcement;

        if ($announcement === null) {
            $this->form->setDefaults();

            return;
        }

        $this->form->setAnnouncement($announcement);
    }

    /**
     * Save the announcement or its new lots with their devices, and open its detail.
     */
    public function save(ReceiveSecurityDeviceLots $receiveLots): void
    {
        /** @var User $user */
        $user = Auth::user();
        $lotsCount = count($this->form->lots);

        if ($this->announcement === null) {
            $announcement = $this->form->store($receiveLots, $user);
        } else {
            $announcement = $this->form->addLots($this->announcement, $receiveLots, $user) ? $this->announcement : null;
        }

        if ($announcement === null) {
            return;
        }

        $devices = number_format((int) $announcement->lots()->reorder('id', 'desc')->take($lotsCount)->get()->sum('quantity'), 0, ',', ' ');

        session()->flash('toast', [
            'icon' => 'success',
            'title' => $this->announcement === null ? 'Anuncio guardado' : 'Lotes agregados',
            'text' => sprintf(
                'Anuncio de ingreso de lotes %s: %s con %s dispositivos.',
                $announcement->number(),
                $lotsCount === 1 ? '1 lote nuevo' : $lotsCount.' lotes nuevos',
                $devices,
            ),
        ]);

        $this->redirectRoute('intranet.security-device-announcements.show', $announcement, navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.security-device-announcements.create-form', [
            'savedLots' => $this->announcement?->lots()->with('securityDeviceType')->get() ?? collect(),
            'cancelUrl' => $this->announcement === null
                ? route('intranet.security-device-announcements.index')
                : route('intranet.security-device-announcements.show', $this->announcement),
        ]);
    }
}
