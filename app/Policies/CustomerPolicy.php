<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use App\Services\TenantContext;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('customers.view');
    }

    public function view(User $user, Customer $customer): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return (int)$customer->business_id === (int)$currentBusinessId && $user->hasPermissionTo('customers.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('customers.manage') || $user->hasPermissionTo('sales.create');
    }

    public function update(User $user, Customer $customer): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return (int)$customer->business_id === (int)$currentBusinessId && $user->hasPermissionTo('customers.manage');
    }

    public function delete(User $user, Customer $customer): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return (int)$customer->business_id === (int)$currentBusinessId && $user->hasPermissionTo('customers.manage');
    }
}
