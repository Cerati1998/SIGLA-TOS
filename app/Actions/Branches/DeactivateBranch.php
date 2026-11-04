<?php

namespace App\Actions\Branches;

use App\Models\Branch;

class DeactivateBranch
{
    /**
     * Deactivate the branch, unless something depends on it. Returns the reason it was not deactivated, or null if it was.
     */
    public function handle(Branch $branch): ?string
    {
        $activeUsers = $branch->users()->count();

        if ($activeUsers > 0) {
            $users = $activeUsers === 1 ? '1 usuario activo. Muévelo' : "{$activeUsers} usuarios activos. Muévelos";

            return "Tiene {$users} a otra sucursal antes de desactivarla.";
        }

        if (Branch::query()->count() === 1) {
            return 'Es la única sucursal activa: sin ella no se podrían crear usuarios.';
        }

        $branch->delete();

        return null;
    }
}
