<?php

namespace App\Livewire\SecurityDeviceAnnouncements;

use App\Concerns\OffersSecurityDeviceLotOrigins;
use App\Livewire\Forms\SecurityDeviceAnnouncementForm;
use App\Models\Provider;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The correction of the header of an announcement of the entry of lots (H37, criterion 12). Its lots are corrected apart.
 *
 * @property-read Collection<int, ShippingLine> $shippingLines
 * @property-read Collection<int, Provider> $providers
 * @property-read Collection<int, SecurityDeviceType> $securityDeviceTypes
 * @property-read array<int, array{usesSeries: bool, requiresApproval: bool}> $securityDeviceTypeBoxes
 */
class EditForm extends Component
{
    use OffersSecurityDeviceLotOrigins;

    #[Locked]
    public SecurityDeviceAnnouncement $announcement;

    public SecurityDeviceAnnouncementForm $form;

    /**
     * Mount the component with the announcement being corrected.
     */
    public function mount(SecurityDeviceAnnouncement $announcement): void
    {
        $this->announcement = $announcement;
        $this->form->setAnnouncement($announcement);
    }

    /**
     * Save the header and go back to the detail of the announcement.
     */
    public function save(): void
    {
        $this->form->update($this->announcement);

        session()->flash('toast', [
            'icon' => 'success',
            'title' => 'Anuncio corregido',
            'text' => 'Se guardaron los cambios del anuncio de ingreso de lotes '.$this->announcement->number().'.',
        ]);

        $this->redirectRoute('intranet.security-device-announcements.show', $this->announcement, navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.security-device-announcements.edit-form', [
            'lotsCount' => $this->announcement->lots()->count(),
        ]);
    }
}
