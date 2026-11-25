<?php

namespace App\Livewire\Inspections;

use App\Actions\Certifications\ListExtraDevices;
use App\Actions\Certifications\ReplaceSecurityDevice;
use App\Enums\CertificationSectionType;
use App\Livewire\Forms\SecurityDeviceReplacementForm;
use App\Models\Certification;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * The extra devices of a unit, sent by a bulk dispatch for the operation, and the window that replaces a device of the
 * kit by one of them (H38, criteria 3 to 5). The list and the window open in the browser, from the inspection, and
 * filter the devices there: the server is called only to replace. Once replaced, the inspection reloads its sections.
 *
 * @phpstan-import-type Extra from ListExtraDevices
 * @phpstan-import-type Placed from ListExtraDevices
 */
class ExtraDevices extends Component
{
    use WithFileUploads;

    #[Locked]
    public Certification $certification;

    #[Locked]
    public CertificationSectionType $type;

    public SecurityDeviceReplacementForm $form;

    /**
     * The extra devices of the unit and the devices of the kit placed in this inspection, which the browser lists and
     * filters by type.
     *
     * @var array{extras: list<Extra>, placed: list<Placed>}
     */
    #[Locked]
    public array $devices = ['extras' => [], 'placed' => []];

    /**
     * Load the devices of the unit.
     */
    public function mount(ListExtraDevices $listExtraDevices): void
    {
        $this->devices = $listExtraDevices->handle($this->certification, $this->type);
    }

    /**
     * Replace the device, tell the inspection to reload its sections and close the window.
     */
    public function replace(ReplaceSecurityDevice $replaceDevice, ListExtraDevices $listExtraDevices): void
    {
        /** @var User $user */
        $user = Auth::user();
        $refusal = $this->form->store($replaceDevice, $this->certification->refresh(), $this->type, $user);

        if ($refusal !== null) {
            $this->dispatch('toast', icon: 'warning', title: 'No se reemplazó', text: $refusal);

            return;
        }

        $this->form->reset();
        $this->devices = $listExtraDevices->handle($this->certification, $this->type);
        $this->dispatch('security-device-replaced');
        $this->dispatch('toast', icon: 'success', title: 'Dispositivo reemplazado', text: 'El que salió pasó a Descartado. Toma al menos una foto nueva de la sección.');
    }

    /**
     * Clear the errors of the window when it is closed with some.
     */
    public function clearErrors(): void
    {
        $this->form->resetValidation();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.inspections.extra-devices');
    }
}
