<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditLogger;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('guest.login');
    }

    public function store(LoginRequest $request, TenantContext $tenantContext): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        // Resolve active business
        $business = $tenantContext->getCurrentBusiness();

        // Audit Log
        AuditLogger::log(
            action: 'user.login',
            entityType: 'User',
            entityId: $user->id,
            newValues: ['email' => $user->email],
            businessId: $business?->id,
            userId: $user->id
        );

        return redirect()->intended(route('app.dashboard'));
    }
}
