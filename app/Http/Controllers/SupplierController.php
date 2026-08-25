<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\SupplierService;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(
        protected SupplierService $supplierService,
        protected TenantContext $tenantContext
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Supplier::class);

        $business = $this->tenantContext->getCurrentBusiness();
        $filters = $request->only(['search', 'status']);
        $suppliers = $this->supplierService->getFiltered($business, $filters, 20);
        $summary = $this->supplierService->getSummary($business);

        return view('app.suppliers.index', [
            'business' => $business,
            'suppliers' => $suppliers,
            'summary' => $summary,
            'user' => Auth::user(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Supplier::class);
        $business = $this->tenantContext->getCurrentBusiness();

        return view('app.suppliers.create', [
            'business' => $business,
            'user' => Auth::user(),
        ]);
    }

    public function store(SupplierRequest $request): JsonResponse|RedirectResponse
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $supplier = $this->supplierService->createSupplier($request->validated(), $business, Auth::user());

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Supplier created successfully.',
                'supplier' => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'company_name' => $supplier->company_name,
                ],
            ]);
        }

        return redirect()->route('app.suppliers.show', $supplier->id)
            ->with('success', 'Supplier "' . $supplier->name . '" created successfully.');
    }

    public function show(Supplier $supplier): View
    {
        $this->authorize('view', $supplier);

        $business = $this->tenantContext->getCurrentBusiness();
        $purchases = Purchase::where('business_id', $business->id)
            ->where('supplier_id', $supplier->id)
            ->orderByDesc('purchased_at')
            ->paginate(15);

        return view('app.suppliers.show', [
            'supplier' => $supplier,
            'business' => $business,
            'purchases' => $purchases,
            'user' => Auth::user(),
        ]);
    }

    public function edit(Supplier $supplier): View
    {
        $this->authorize('update', $supplier);
        $business = $this->tenantContext->getCurrentBusiness();

        return view('app.suppliers.edit', [
            'supplier' => $supplier,
            'business' => $business,
            'user' => Auth::user(),
        ]);
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('update', $supplier);

        $this->supplierService->updateSupplier($supplier, $request->validated(), Auth::user());

        return redirect()->route('app.suppliers.show', $supplier->id)
            ->with('success', 'Supplier details updated successfully.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('delete', $supplier);

        $this->supplierService->archiveSupplier($supplier, Auth::user());

        return redirect()->route('app.suppliers.index')
            ->with('success', 'Supplier has been archived.');
    }
}
