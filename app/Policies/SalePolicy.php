<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use App\Services\TenantContext;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('sales.view');
    }

    public function view(User $user, Sale $sale): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();

        if ((int)$sale->business_id !== (int)$currentBusinessId) {
            return false;
        }

        if (!$user->hasPermissionTo('sales.view')) {
            return false;
        }

        // Owners and managers can view all business sales; cashiers can view their own sales
        if ($user->isOwner() || $user->isManager()) {
            return true;
        }

        return (int)$sale->user_id === (int)$user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('sales.create');
    }

    public function void(User $user, Sale $sale): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();

        if ((int)$sale->business_id !== (int)$currentBusinessId) {
            return false;
        }

        if ($sale->status !== Sale::STATUS_COMPLETED) {
            return false;
        }

        // Only Owner or Manager with permission can void sales; Cashiers are strictly forbidden
        return ($user->isOwner() || $user->isManager()) && $user->hasPermissionTo('sales.delete');
    }

    public function applyDiscount(User $user): bool
    {
        return $user->isOwner() || $user->isManager() || $user->hasPermissionTo('sales.create');
    }
}
