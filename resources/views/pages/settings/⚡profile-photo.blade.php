<?php

use App\Actions\Profile\UpdateProfilePhoto;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ?TemporaryUploadedFile $photo = null;

    /**
     * Save the photo as soon as the user picks it.
     */
    public function updatedPhoto(): void
    {
        $this->validate(['photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']]);

        (new UpdateProfilePhoto)->handle(Auth::user(), $this->photo);

        $this->redirect(route('intranet.profile.edit'), navigate: true);
    }
}; ?>

<div class="mt-6 flex flex-wrap items-center gap-4">
    <img src="{{ Auth::user()->profilePhotoUrl() }}" alt="Foto de perfil" class="size-16 rounded-card bg-brand/10 object-cover">

    <div class="flex flex-col gap-1.75">
        <input x-ref="photo" type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp" aria-label="Foto de perfil" class="hidden" data-test="profile-photo-input">

        <div>
            <flux:button type="button" x-on:click="$refs.photo.click()" wire:loading.attr="disabled" wire:target="photo">
                <span wire:loading.remove wire:target="photo">Cambiar foto</span>
                <span wire:loading wire:target="photo">Subiendo…</span>
            </flux:button>
        </div>

        <span class="text-sm text-ink-muted">JPG, PNG o WEBP, hasta 2 MB.</span>

        @error('photo')
            <span role="alert" class="text-sm text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>
