<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    public function createExpense(array $data, Business $business, User $user): Expense
    {
        return DB::transaction(function () use ($data, $business, $user) {
            $expense = Expense::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'category_id' => $data['category_id'] ?? null,
                'description' => $data['description'],
                'amount' => (float)$data['amount'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'reference' => $data['reference'] ?? null,
                'expense_date' => $data['expense_date'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
                'status' => Expense::STATUS_ACTIVE,
            ]);

            AuditLogger::log(
                action: 'expense.created',
                entityType: Expense::class,
                entityId: $expense->id,
                newValues: [
                    'description' => $expense->description,
                    'amount' => $expense->amount,
                ],
                businessId: $business->id,
                userId: $user->id
            );

            return $expense;
        });
    }

    public function updateExpense(Expense $expense, array $data, User $user): Expense
    {
        return DB::transaction(function () use ($expense, $data, $user) {
            $oldValues = $expense->only(['description', 'amount', 'category_id', 'payment_method', 'expense_date']);
            $expense->update($data);

            AuditLogger::log(
                action: 'expense.updated',
                entityType: Expense::class,
                entityId: $expense->id,
                oldValues: $oldValues,
                newValues: $expense->only(array_keys($oldValues)),
                businessId: $expense->business_id,
                userId: $user->id
            );

            return $expense;
        });
    }

    public function voidExpense(Expense $expense, string $reason, User $user): Expense
    {
        if ($expense->status !== Expense::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'expense' => 'Only active expenses can be voided.',
            ]);
        }

        return DB::transaction(function () use ($expense, $reason, $user) {
            $expense->update([
                'status' => Expense::STATUS_VOIDED,
                'void_reason' => $reason,
                'voided_by' => $user->id,
                'voided_at' => now(),
            ]);

            AuditLogger::log(
                action: 'expense.voided',
                entityType: Expense::class,
                entityId: $expense->id,
                oldValues: ['status' => 'active'],
                newValues: ['status' => 'voided', 'reason' => $reason],
                businessId: $expense->business_id,
                userId: $user->id
            );

            return $expense;
        });
    }

    public function getFiltered(Business $business, array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Expense::with(['category', 'user'])
            ->where('business_id', $business->id);

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', $search)
                  ->orWhere('reference', 'like', $search);
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category_id']) && $filters['category_id'] !== 'all') {
            $query->where('category_id', $filters['category_id']);
        }

        $dateFilter = $filters['date_range'] ?? 'all';
        switch ($dateFilter) {
            case 'today':
                $query->whereDate('expense_date', now()->toDateString());
                break;
            case 'yesterday':
                $query->whereDate('expense_date', now()->subDay()->toDateString());
                break;
            case 'this_week':
                $query->whereBetween('expense_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
                break;
            case 'this_month':
                $query->whereBetween('expense_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);
                break;
        }

        return $query->orderByDesc('expense_date')->paginate($perPage)->withQueryString();
    }

    public function getSummary(Business $business): array
    {
        $today = Expense::where('business_id', $business->id)
            ->where('status', Expense::STATUS_ACTIVE)
            ->whereDate('expense_date', now()->toDateString());

        $thisWeek = Expense::where('business_id', $business->id)
            ->where('status', Expense::STATUS_ACTIVE)
            ->whereBetween('expense_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);

        $thisMonth = Expense::where('business_id', $business->id)
            ->where('status', Expense::STATUS_ACTIVE)
            ->whereBetween('expense_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);

        return [
            'today_total' => (float)(clone $today)->sum('amount'),
            'week_total' => (float)(clone $thisWeek)->sum('amount'),
            'month_total' => (float)(clone $thisMonth)->sum('amount'),
        ];
    }

    // Category CRUD
    public function createCategory(array $data, Business $business, User $user): ExpenseCategory
    {
        return DB::transaction(function () use ($data, $business, $user) {
            $cat = ExpenseCategory::create([
                'business_id' => $business->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => 'active',
            ]);

            AuditLogger::log(
                action: 'expense_category.created',
                entityType: ExpenseCategory::class,
                entityId: $cat->id,
                newValues: ['name' => $cat->name],
                businessId: $business->id,
                userId: $user->id
            );

            return $cat;
        });
    }

    public function updateCategory(ExpenseCategory $category, array $data, User $user): ExpenseCategory
    {
        return DB::transaction(function () use ($category, $data, $user) {
            $category->update($data);
            return $category;
        });
    }

    public function archiveCategory(ExpenseCategory $category, User $user): ExpenseCategory
    {
        return DB::transaction(function () use ($category, $user) {
            $category->delete();
            return $category;
        });
    }
}
