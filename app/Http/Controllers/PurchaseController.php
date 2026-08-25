<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purchases\CreatePurchaseRequest;
use App\Http\Requests\Purchases\VoidPurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService,
        protected TenantContext $tenantContext
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Purchase::class);

        $business = $this->tenantContext->getCurrentBusiness();
        $filters = $request->only(['search', 'status', 'supplier_id', 'date_range']);
        $purchases = $this->purchaseService->getFiltered($business, $filters, 20);
        $summary = $this->purchaseService->getSummary($business);
        $suppliers = Supplier::where('business_id', $business->id)->where('status', 'active')->get();

        return view('app.purchases.index', [
            'business' => $business,
            'purchases' => $purchases,
            'summary' => $summary,
            'suppliers' => $suppliers,
            'user' => Auth::user(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Purchase::class);

        $business = $this->tenantContext->getCurrentBusiness();
        $suppliers = Supplier::where('business_id', $business->id)->where('status', 'active')->get();
        $products = Product::where('business_id', $business->id)->where('status', 'active')->get();

        return view('app.purchases.create', [
            'business' => $business,
            'suppliers' => $suppliers,
            'products' => $products,
            'user' => Auth::user(),
        ]);
    }

    public function store(CreatePurchaseRequest $request): RedirectResponse
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $purchase = $this->purchaseService->createPurchase($request->validated(), $business, Auth::user());

        return redirect()->route('app.purchases.show', $purchase->id)
            ->with('success', 'Purchase #' . $purchase->purchase_number . ' completed and inventory increased.');
    }

    public function show(Purchase $purchase): View
    {
        $this->authorize('view', $purchase);

        $business = $this->tenantContext->getCurrentBusiness();
        $purchase->load(['items.product', 'supplier', 'user', 'voidedByUser']);

        return view('app.purchases.show', [
            'purchase' => $purchase,
            'business' => $business,
            'user' => Auth::user(),
        ]);
    }

    public function receipt(Purchase $purchase): View
    {
        $this->authorize('view', $purchase);

        $business = $this->tenantContext->getCurrentBusiness();
        $purchase->load(['items', 'supplier', 'user']);

        return view('app.purchases.receipt', [
            'purchase' => $purchase,
            'business' => $business,
            'user' => Auth::user(),
        ]);
    }

    public function void(VoidPurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('void', $purchase);

        $this->purchaseService->voidPurchase($purchase, $request->reason, Auth::user());

        return redirect()->route('app.purchases.show', $purchase->id)
            ->with('success', 'Purchase #' . $purchase->purchase_number . ' has been voided and inventory reversed.');
    }
}
