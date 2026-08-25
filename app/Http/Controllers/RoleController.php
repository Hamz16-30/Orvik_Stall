<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::with(['permissions', 'memberships'])->get();
        $permissions = Permission::all()->groupBy('module');

        return view('app.settings.roles.index', [
            'roles' => $roles,
            'groupedPermissions' => $permissions,
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'is_system' => false,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        AuditLogger::log(
            action: 'role.created',
            entityType: Role::class,
            entityId: $role->id,
            newValues: ['name' => $role->name, 'permissions' => $request->permissions]
        );

        return back()->with('success', 'Custom role created successfully.');
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $role->update([
            'display_name' => $request->display_name,
            'description' => $request->description,
        ]);

        // If not Owner system role, allow updating permissions
        if ($role->name !== Role::OWNER && $request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        AuditLogger::log(
            action: 'role.updated',
            entityType: Role::class,
            entityId: $role->id,
            newValues: ['name' => $role->name, 'permissions' => $request->permissions]
        );

        return back()->with('success', 'Role permissions updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        if ($role->isSystemRole()) {
            return back()->withErrors(['error' => 'System roles cannot be deleted.']);
        }

        if ($role->memberships()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete role assigned to active staff members. Please reassign their roles first.']);
        }

        $role->delete();

        AuditLogger::log(
            action: 'role.deleted',
            entityType: Role::class,
            entityId: $role->id,
            oldValues: ['name' => $role->name]
        );

        return back()->with('success', 'Role deleted.');
    }
}
