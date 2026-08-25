<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function destroy(Request $request, TenantContext $tenantContext): RedirectResponse
    {
        $user = Auth::user();
        $businessId = $tenantContext->getCurrentBusinessId();

        if ($user) {
            AuditLogger::log(
                action: 'user.logout',
                entityType: 'User',
                entityId: $user->id,
                businessId: $businessId,
                userId: $user->id
            );
        }

        Auth::guard('web')->logout();

        $tenantContext->clear();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been logged out securely.');
    }
}
