<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\UpdateStaffRoleRequest;
use App\Models\BusinessUser;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        $this->authorize('viewAny', User::class);

        $business = $tenantContext->getCurrentBusiness();

        $staffMembers = BusinessUser::with(['user', 'role'])
            ->where('business_id', $business->id)
            ->get();

        $invitations = Invitation::with('role')
            ->where('business_id', $business->id)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->get();

        $roles = Role::all();

        return view('app.settings.staff.index', [
            'business' => $business,
            'staffMembers' => $staffMembers,
            'invitations' => $invitations,
            'roles' => $roles,
        ]);
    }

    public function updateRole(UpdateStaffRoleRequest $request, User $user, TenantContext $tenantContext): RedirectResponse
    {
        $business = $tenantContext->getCurrentBusiness();

        $this->authorize('updateRole', [User::class, $user, $business]);

        $membership = BusinessUser::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $oldRole = $membership->role_id;
        $membership->update(['role_id' => $request->role_id]);

        AuditLogger::log(
            action: 'staff.role_updated',
            entityType: User::class,
            entityId: $user->id,
            oldValues: ['role_id' => $oldRole],
            newValues: ['role_id' => $request->role_id]
        );

        return back()->with('success', 'Staff role updated successfully.');
    }

    public function suspend(User $user, TenantContext $tenantContext): RedirectResponse
    {
        $business = $tenantContext->getCurrentBusiness();

        $this->authorize('suspend', [User::class, $user, $business]);

        $membership = BusinessUser::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $newStatus = $membership->status === 'suspended' ? 'active' : 'suspended';
        $membership->update(['status' => $newStatus]);

        AuditLogger::log(
            action: $newStatus === 'suspended' ? 'staff.suspended' : 'staff.unsuspended',
            entityType: User::class,
            entityId: $user->id,
            newValues: ['status' => $newStatus]
        );

        return back()->with('success', 'Staff member account status changed to ' . $newStatus . '.');
    }

    public function deactivate(User $user, TenantContext $tenantContext): RedirectResponse
    {
        $business = $tenantContext->getCurrentBusiness();

        $this->authorize('deactivate', [User::class, $user, $business]);

        $membership = BusinessUser::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $membership->update(['status' => 'deactivated']);

        AuditLogger::log(
            action: 'staff.deactivated',
            entityType: User::class,
            entityId: $user->id
        );

        return back()->with('success', 'Staff member access deactivated.');
    }
}
