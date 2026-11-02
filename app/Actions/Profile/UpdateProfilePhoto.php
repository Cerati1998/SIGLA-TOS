<?php

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateProfilePhoto
{
    /**
     * Store the new profile photo and delete the one it replaces.
     */
    public function handle(User $user, UploadedFile $photo): void
    {
        $previousPath = $user->profile_photo_path;

        $user->forceFill(['profile_photo_path' => $photo->store('profile-photos', 'public')])->save();

        if ($previousPath !== null) {
            Storage::disk('public')->delete($previousPath);
        }
    }
}
