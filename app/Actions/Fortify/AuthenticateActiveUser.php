<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;

class AuthenticateActiveUser
{
    /**
     * Find the user that matches the credentials, only if the account is active.
     */
    public function __invoke(Request $request): ?User
    {
        $user = User::query()
            ->where(Fortify::username(), $request->string(Fortify::username())->toString())
            ->first();

        if ($user === null || ! Hash::check($request->string('password')->toString(), $user->password)) {
            return null;
        }

        return $user->is_active ? $user : null;
    }
}
