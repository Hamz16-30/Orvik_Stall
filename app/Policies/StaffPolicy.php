<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class StaffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('staff.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('staff.manage');
    }

    public function updateRole(User $user, User $targetUser, Business $business): bool
    {
        if (!$user->hasPermissionTo('staff.manage')) {
            return false;
        }

        // Cannot change role of the business owner
        if ((int)$business->owner_id === (int)$targetUser->id) {
            return false;
        }

        return true;
    }

    public function suspend(User $user, User $targetUser, Business $business): bool
    {
        if (!$user->hasPermissionTo('staff.manage')) {
            return false;
        }

        // Cannot suspend owner or self
        if ((int)$business->owner_id === (int)$targetUser->id || (int)$user->id === (int)$targetUser->id) {
            return false;
        }

        return true;
    }

    public function deactivate(User $user, User $targetUser, Business $business): bool
    {
        if (!$user->hasPermissionTo('staff.manage')) {
            return false;
        }

        // Cannot deactivate owner or self
        if ((int)$business->owner_id === (int)$targetUser->id || (int)$user->id === (int)$targetUser->id) {
            return false;
        }

        return true;
    }
}
