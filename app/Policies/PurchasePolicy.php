<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;
use App\Services\TenantContext;

class PurchasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('purchases.view');
    }

    public function view(User $user, Purchase $purchase): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return (int)$purchase->business_id === (int)$currentBusinessId && $user->hasPermissionTo('purchases.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('purchases.create');
    }

    public function void(User $user, Purchase $purchase): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return (int)$purchase->business_id === (int)$currentBusinessId &&
            $purchase->status === Purchase::STATUS_COMPLETED &&
            $user->hasPermissionTo('purchases.void');
    }
}
