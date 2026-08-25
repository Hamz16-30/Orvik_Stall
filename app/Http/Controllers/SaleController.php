<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sales\VoidSaleRequest;
use App\Models\Sale;
use App\Services\SalesService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(
        protected SalesService $salesService,
        protected TenantContext $tenantContext
    ) {}

    public function history(Request $request): View
    {
        $this->authorize('viewAny', Sale::class);

        $business = $this->tenantContext->getCurrentBusiness();
        $user = Auth::user();

        $dailySummary = $this->salesService->getDailySummary($business);

        $query = Sale::with(['user', 'items'])
            ->where('business_id', $business->id);

        // If Cashier without permission to view all sales, scope to own sales
        if (!$user->isOwner() && !$user->isManager()) {
            $query->where('user_id', $user->id);
        }

        // Search Filter (Invoice, Notes)
        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', $search)
                  ->orWhere('notes', 'like', $search)
                  ->orWhereHas('items', function ($iq) use ($search) {
                      $iq->where('product_name', 'like', $search);
                  });
            });
        }

        // Payment Method Filter
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Date Range Filter
        $dateFilter = $request->query('date_range', 'all');
        switch ($dateFilter) {
            case 'today':
                $query->whereDate('completed_at', now()->toDateString());
                break;
            case 'yesterday':
                $query->whereDate('completed_at', now()->subDay()->toDateString());
                break;
            case 'this_week':
                $query->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()]);
                break;
        }

        $sales = $query->orderByDesc('completed_at')->paginate(20)->withQueryString();

        return view('app.sales.history', [
            'business' => $business,
            'sales' => $sales,
            'dailySummary' => $dailySummary,
            'user' => $user,
        ]);
    }

    public function show(Sale $sale): View
    {
        $this->authorize('view', $sale);

        $business = $this->tenantContext->getCurrentBusiness();
        $sale->load(['items.product', 'user', 'voidedByUser']);

        return view('app.sales.show', [
            'sale' => $sale,
            'business' => $business,
            'user' => Auth::user(),
        ]);
    }

    public function receipt(Sale $sale): View
    {
        $this->authorize('view', $sale);

        $business = $this->tenantContext->getCurrentBusiness();
        $sale->load(['items', 'user']);

        return view('app.sales.receipt', [
            'sale' => $sale,
            'business' => $business,
            'user' => Auth::user(),
        ]);
    }

    public function void(VoidSaleRequest $request, Sale $sale): RedirectResponse
    {
        $this->authorize('void', $sale);

        $this->salesService->voidSale($sale, $request->reason, Auth::user());

        return redirect()->route('app.sales.show', $sale->id)
            ->with('success', 'Sale #' . $sale->invoice_number . ' has been voided and inventory returned to stock.');
    }
}
