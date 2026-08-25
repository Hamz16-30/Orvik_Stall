<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use App\Services\TenantContext;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('expenses.view');
    }

    public function view(User $user, Expense $expense): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return (int)$expense->business_id === (int)$currentBusinessId && $user->hasPermissionTo('expenses.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('expenses.create');
    }

    public function update(User $user, Expense $expense): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return (int)$expense->business_id === (int)$currentBusinessId &&
            $expense->status === Expense::STATUS_ACTIVE &&
            $user->hasPermissionTo('expenses.create');
    }

    public function void(User $user, Expense $expense): bool
    {
        $currentBusinessId = app(TenantContext::class)->getCurrentBusinessId();
        return (int)$expense->business_id === (int)$currentBusinessId &&
            $expense->status === Expense::STATUS_ACTIVE &&
            $user->hasPermissionTo('expenses.void');
    }
}
