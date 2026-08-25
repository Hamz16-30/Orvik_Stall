<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expenses\ExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use App\Services\ExpenseService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function __construct(
        protected ExpenseService $expenseService,
        protected TenantContext $tenantContext
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', \App\Models\Expense::class);

        $business = $this->tenantContext->getCurrentBusiness();
        $categories = ExpenseCategory::where('business_id', $business->id)
            ->withCount(['expenses' => function ($q) {
                $q->where('status', \App\Models\Expense::STATUS_ACTIVE);
            }])
            ->orderBy('name')
            ->get();

        return view('app.expenses.categories.index', [
            'business' => $business,
            'categories' => $categories,
            'user' => Auth::user(),
        ]);
    }

    public function store(ExpenseCategoryRequest $request): RedirectResponse
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $this->expenseService->createCategory($request->validated(), $business, Auth::user());

        return redirect()->route('app.expenses.categories.index')
            ->with('success', 'Expense category created successfully.');
    }

    public function update(ExpenseCategoryRequest $request, ExpenseCategory $category): RedirectResponse
    {
        $this->expenseService->updateCategory($category, $request->validated(), Auth::user());

        return redirect()->route('app.expenses.categories.index')
            ->with('success', 'Expense category updated successfully.');
    }

    public function destroy(ExpenseCategory $category): RedirectResponse
    {
        $this->expenseService->archiveCategory($category, Auth::user());

        return redirect()->route('app.expenses.categories.index')
            ->with('success', 'Expense category archived.');
    }
}
