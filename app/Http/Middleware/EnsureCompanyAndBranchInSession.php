<?php

namespace App\Http\Middleware;

use App\Actions\Session\StoreCompanyAndBranchInSession;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyAndBranchInSession
{
    public function __construct(private StoreCompanyAndBranchInSession $storeCompanyAndBranch) {}

    /**
     * Put the company and the branch of the signed-in user in the session when they are not there yet,
     * as on the first request after signing in.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && ! $request->session()->has(['company', 'branch'])) {
            $this->storeCompanyAndBranch->handle($user);
        }

        return $next($request);
    }
}
