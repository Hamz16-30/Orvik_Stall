<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function view(User $user, Business $business): bool
    {
        return $business->users()->where('users.id', $user->id)->exists()
            && $user->hasPermissionTo('settings.view');
    }

    public function update(User $user, Business $business): bool
    {
        return $business->users()->where('users.id', $user->id)->exists()
            && $user->hasPermissionTo('settings.update');
    }

    public function transferOwnership(User $user, Business $business): bool
    {
        return (int)$business->owner_id === (int)$user->id;
    }
}
