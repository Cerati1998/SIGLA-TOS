<?php

namespace App\Livewire\Checkpoints;

use App\Actions\Checkpoints\ListPendingCheckpoints;
use App\Actions\Checkpoints\SaveContainerPhoto;
use App\Actions\Checkpoints\StartCheckpoint;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * The units whose checkpoint is pending or half done, to choose one (H34, criteria 1 and 2). The search by container
 * filters the list already loaded, in the browser. The optional photo of the container is uploaded before choosing
 * the unit, and opening the unit saves it in its checkpoint, which is born then if nobody opened it before.
 */
class UnitPicker extends Component
{
    use WithFileUploads;

    /**
     * The photo of the container, compressed in the browser, waiting for the unit to be chosen.
     */
    public ?TemporaryUploadedFile $photo = null;

    /**
     * The thumbnail of the photo of the container, made in the browser.
     */
    public ?TemporaryUploadedFile $thumbnail = null;

    /**
     * Open the checkpoint of a unit, starting it the first time, with the photo of the container if one was taken.
     */
    public function open(int $certificationId, ListPendingCheckpoints $listPendingCheckpoints, StartCheckpoint $startCheckpoint, SaveContainerPhoto $saveContainerPhoto): void
    {
        /** @var User $user */
        $user = Auth::user();
        $checkpoint = $startCheckpoint->handle($listPendingCheckpoints->certification($certificationId), $user);

        if ($this->photo !== null) {
            $this->validate([
                'photo' => ['required', 'image', 'mimes:jpg,jpeg', 'max:3072'],
                'thumbnail' => ['required', 'image', 'mimes:jpg,jpeg', 'max:512'],
            ], [
                'photo.*' => 'La foto del contenedor no es válida. Tómala otra vez.',
                'thumbnail.*' => 'La foto del contenedor no es válida. Tómala otra vez.',
            ]);

            /** @var TemporaryUploadedFile $thumbnail */
            $thumbnail = $this->thumbnail;

            $saveContainerPhoto->handle($checkpoint, $this->photo, $thumbnail, $user);
        }

        $this->redirectRoute('intranet.checkpoints.show', $checkpoint, navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(ListPendingCheckpoints $listPendingCheckpoints): View
    {
        return view('pages.checkpoints.unit-picker', [
            'units' => $listPendingCheckpoints->handle(),
        ]);
    }
}
