<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;
use App\Services\TenantContext;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('suppliers.view');
    }

    public function view(User $user, Supplier $supplier): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return (int)$supplier->business_id === (int)$currentBusinessId && $user->hasPermissionTo('suppliers.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('suppliers.manage');
    }

    public function update(User $user, Supplier $supplier): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return (int)$supplier->business_id === (int)$currentBusinessId && $user->hasPermissionTo('suppliers.manage');
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return (int)$supplier->business_id === (int)$currentBusinessId && $user->hasPermissionTo('suppliers.manage');
    }
}
