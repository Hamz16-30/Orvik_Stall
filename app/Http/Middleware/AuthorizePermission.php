<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthorizePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::user();

        if (!$user || !$user->hasPermissionTo($permission)) {
            abort(403, 'You do not have the required permission (' . $permission . ') to access this resource.');
        }

        return $next($request);
    }
}
