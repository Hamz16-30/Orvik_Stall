<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Services\TenantContext;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('inventory.view');
    }

    public function view(User $user, Category $category): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return $user->hasPermissionTo('inventory.view') && (int)$category->business_id === (int)$currentBusinessId;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('inventory.create');
    }

    public function update(User $user, Category $category): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return $user->hasPermissionTo('inventory.update') && (int)$category->business_id === (int)$currentBusinessId;
    }

    public function delete(User $user, Category $category): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return $user->hasPermissionTo('inventory.delete') && (int)$category->business_id === (int)$currentBusinessId;
    }

    public function restore(User $user, Category $category): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return $user->hasPermissionTo('inventory.delete') && (int)$category->business_id === (int)$currentBusinessId;
    }
}
