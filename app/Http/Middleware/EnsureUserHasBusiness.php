<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasBusiness
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$this->tenantContext->hasCurrentBusiness()) {
            // User has no active business account
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your account is not associated with an active business stall.',
            ]);
        }

        return $next($request);
    }
}
