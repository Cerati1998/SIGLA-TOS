<?php

namespace App\Livewire\Kits;

use App\Actions\Kits\CreateKit;
use App\Livewire\Forms\KitForm;
use App\Models\KitType;
use App\Models\ShippingLine;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read Collection<int, KitType> $kitTypes
 * @property-read Collection<int, ShippingLine> $shippingLines
 */
class CreateForm extends Component
{
    public KitForm $form;

    /**
     * Get the active kit types, to choose one.
     *
     * @return Collection<int, KitType>
     */
    #[Computed]
    public function kitTypes(): Collection
    {
        return KitType::query()->orderBy('name')->get(['id', 'name']);
    }

    /**
     * Get the active shipping lines, to choose one.
     *
     * @return Collection<int, ShippingLine>
     */
    #[Computed]
    public function shippingLines(): Collection
    {
        return ShippingLine::query()->orderBy('code')->get(['id', 'code', 'name']);
    }

    /**
     * Create the kit with its pouch and open it, to scan its other devices.
     */
    public function save(CreateKit $createKit): void
    {
        /** @var User $user */
        $user = Auth::user();

        $kit = $this->form->store($createKit, $user);

        if ($kit === null) {
            return;
        }

        session()->flash('toast', [
            'icon' => 'success',
            'title' => 'Kit creado',
            'text' => sprintf('El kit %s quedó Armando. Escanea sus dispositivos.', $kit->pouch_code),
        ]);

        $this->redirectRoute('intranet.kits.show', $kit, navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.kits.create-form');
    }
}
