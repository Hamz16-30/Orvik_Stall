<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expenses\ExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ExpenseService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseService $expenseService,
        protected TenantContext $tenantContext
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Expense::class);

        $business = $this->tenantContext->getCurrentBusiness();
        $filters = $request->only(['search', 'status', 'category_id', 'date_range']);
        $expenses = $this->expenseService->getFiltered($business, $filters, 20);
        $summary = $this->expenseService->getSummary($business);
        $categories = ExpenseCategory::where('business_id', $business->id)->where('status', 'active')->get();

        return view('app.expenses.index', [
            'business' => $business,
            'expenses' => $expenses,
            'summary' => $summary,
            'categories' => $categories,
            'user' => Auth::user(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Expense::class);

        $business = $this->tenantContext->getCurrentBusiness();
        $categories = ExpenseCategory::where('business_id', $business->id)->where('status', 'active')->get();

        return view('app.expenses.create', [
            'business' => $business,
            'categories' => $categories,
            'user' => Auth::user(),
        ]);
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $this->expenseService->createExpense($request->validated(), $business, Auth::user());

        return redirect()->route('app.expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    public function void(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorize('void', $expense);

        $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        $this->expenseService->voidExpense($expense, $request->reason, Auth::user());

        return redirect()->route('app.expenses.index')
            ->with('success', 'Expense has been voided.');
    }
}
