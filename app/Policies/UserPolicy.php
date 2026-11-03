<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can deactivate the given user.
     */
    public function delete(User $user, User $model): bool
    {
        return ! $user->is($model);
    }

    /**
     * Determine whether the user can reactivate the given user.
     */
    public function restore(User $user, User $model): bool
    {
        return ! $user->is($model);
    }
}
