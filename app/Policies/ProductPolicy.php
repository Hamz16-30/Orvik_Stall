<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use App\Services\TenantContext;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('inventory.view');
    }

    public function view(User $user, Product $product): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return $user->hasPermissionTo('inventory.view') && (int)$product->business_id === (int)$currentBusinessId;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('inventory.create');
    }

    public function update(User $user, Product $product): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return $user->hasPermissionTo('inventory.update') && (int)$product->business_id === (int)$currentBusinessId;
    }

    public function delete(User $user, Product $product): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return $user->hasPermissionTo('inventory.delete') && (int)$product->business_id === (int)$currentBusinessId;
    }

    public function restore(User $user, Product $product): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return $user->hasPermissionTo('inventory.delete') && (int)$product->business_id === (int)$currentBusinessId;
    }

    public function adjustStock(User $user, Product $product): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return ($user->isOwner() || $user->isManager())
            && $user->hasPermissionTo('inventory.update')
            && (int)$product->business_id === (int)$currentBusinessId;
    }

    public function viewCostPrice(User $user): bool
    {
        return $user->isOwner() || $user->isManager();
    }
}
