<?php

namespace App\Livewire\Settings;

use App\Actions\Profile\UpdateProfilePhoto;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ProfilePhoto extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $photo = null;

    /**
     * Save the photo as soon as the user picks it.
     */
    public function updatedPhoto(): void
    {
        $this->validate(['photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']]);

        (new UpdateProfilePhoto)->handle(Auth::user(), $this->photo);

        $this->redirect(route('profile.edit'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.settings.profile-photo');
    }
}
