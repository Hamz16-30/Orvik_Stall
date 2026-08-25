<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\InviteStaffRequest;
use App\Models\BusinessUser;
use App\Models\Invitation;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SaaS\UsageService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function store(InviteStaffRequest $request, TenantContext $tenantContext, UsageService $usageService): RedirectResponse
    {
        $business = $tenantContext->getCurrentBusiness();

        if (!$usageService->canCreateUser($business)) {
            $usage = $usageService->getUserUsage($business);
            return back()->withInput()->withErrors([
                'plan_limit' => "You have reached your plan's maximum allowed limit of {$usage['limit']} staff users. Upgrade your plan to invite more team members.",
            ]);
        }

        // Check if user is already a member
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            $isMember = BusinessUser::where('business_id', $business->id)
                ->where('user_id', $existingUser->id)
                ->exists();

            if ($isMember) {
                return back()->withErrors(['email' => 'This user is already a member of this stall.']);
            }
        }

        // Cancel previous pending invitations for this email in this business
        Invitation::where('business_id', $business->id)
            ->where('email', $request->email)
            ->whereNull('accepted_at')
            ->delete();

        $invitation = Invitation::create([
            'business_id' => $business->id,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'token' => Invitation::generateToken(),
            'expires_at' => now()->addDays(7),
            'invited_by' => Auth::id(),
        ]);

        AuditLogger::log(
            action: 'staff.invited',
            entityType: Invitation::class,
            entityId: $invitation->id,
            newValues: ['email' => $invitation->email, 'role_id' => $invitation->role_id]
        );

        $acceptUrl = route('invitations.accept', ['token' => $invitation->token]);

        return back()->with('success', 'Staff invitation created successfully! Direct acceptance link: ' . $acceptUrl);
    }

    public function cancel(Invitation $invitation, TenantContext $tenantContext): RedirectResponse
    {
        $business = $tenantContext->getCurrentBusiness();

        if ((int)$invitation->business_id !== (int)$business->id) {
            abort(403);
        }

        $invitation->delete();

        AuditLogger::log(
            action: 'staff.invitation_cancelled',
            entityType: Invitation::class,
            entityId: $invitation->id,
            oldValues: ['email' => $invitation->email]
        );

        return back()->with('info', 'Invitation cancelled.');
    }

    public function showAccept(string $token): View
    {
        $invitation = Invitation::with(['business', 'role'])
            ->where('token', $token)
            ->first();

        if (!$invitation || !$invitation->isValid()) {
            abort(403, 'This invitation is invalid, has expired, or has already been used.');
        }

        return view('guest.invitations.accept', [
            'invitation' => $invitation,
        ]);
    }

    public function processAccept(Request $request, string $token, TenantContext $tenantContext): RedirectResponse
    {
        $invitation = Invitation::with(['business', 'role'])
            ->where('token', $token)
            ->first();

        if (!$invitation || !$invitation->isValid()) {
            abort(403, 'This invitation is invalid or expired.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request, $invitation, $tenantContext) {
            $user = User::firstOrCreate(
                ['email' => $invitation->email],
                [
                    'name' => $request->name,
                    'password' => Hash::make($request->password),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            // Create Membership
            BusinessUser::updateOrCreate(
                ['business_id' => $invitation->business_id, 'user_id' => $user->id],
                [
                    'role_id' => $invitation->role_id,
                    'status' => 'active',
                    'joined_at' => now(),
                ]
            );

            // Mark invitation accepted
            $invitation->update(['accepted_at' => now()]);

            // Set context
            $tenantContext->setCurrentBusiness($invitation->business);

            // Audit
            AuditLogger::log(
                action: 'staff.invitation_accepted',
                entityType: User::class,
                entityId: $user->id,
                newValues: ['email' => $user->email, 'business_id' => $invitation->business_id],
                businessId: $invitation->business_id,
                userId: $user->id
            );

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('app.dashboard')->with('success', 'Welcome to ' . $invitation->business->name . '!');
    }
}
