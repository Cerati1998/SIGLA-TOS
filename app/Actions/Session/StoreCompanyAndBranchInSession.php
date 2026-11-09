<?php

namespace App\Actions\Session;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StoreCompanyAndBranchInSession
{
    /**
     * Put the company and the branch of the user in the session, as `company` and `branch`.
     * Only the id and the name are stored: the company also holds credentials that must never reach the session.
     */
    public function handle(User $user): void
    {
        /** @var Branch $branch */
        $branch = $user->branch()->withTrashed()->with('company')->firstOrFail();

        session([
            'company' => [
                'id' => $branch->company->id,
                'name' => $branch->company->trade_name ?: $branch->company->business_name,
            ],
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
            ],
        ]);
    }

    /**
     * Refresh the company and the branch in the session of the signed-in user, after one of them was edited.
     */
    public function forSignedInUser(): void
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $this->handle($user);
        }
    }
}
