<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sales\CompleteSaleRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SalesService;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class POSController extends Controller
{
    public function __construct(
        protected SalesService $salesService,
        protected TenantContext $tenantContext
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('create', Sale::class);

        $business = $this->tenantContext->getCurrentBusiness();
        $categories = Category::where('business_id', $business->id)->where('status', 'active')->get();

        // Query active sellable products
        $productsQuery = Product::where('business_id', $business->id)
            ->where('status', 'active')
            ->orderBy('name');

        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $productsQuery->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('sku', 'like', $search)
                  ->orWhere('barcode', 'like', $search);
            });
        }

        $products = $productsQuery->get();

        return view('app.sales.index', [
            'business' => $business,
            'categories' => $categories,
            'products' => $products,
            'user' => Auth::user(),
        ]);
    }

    public function store(CompleteSaleRequest $request): JsonResponse|RedirectResponse
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $sale = $this->salesService->completeSale($request->validated(), $business, Auth::user());

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully.',
                'sale' => [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'total_amount' => number_format($sale->total_amount, 2),
                    'amount_received' => $sale->amount_received ? number_format($sale->amount_received, 2) : null,
                    'change_amount' => $sale->change_amount ? number_format($sale->change_amount, 2) : '0.00',
                    'payment_method' => ucfirst($sale->payment_method),
                    'receipt_url' => route('app.sales.receipt', $sale->id),
                    'show_url' => route('app.sales.show', $sale->id),
                ],
            ]);
        }

        return redirect()->route('app.sales.show', $sale->id)
            ->with('success', 'Sale #' . $sale->invoice_number . ' completed successfully.');
    }
}
