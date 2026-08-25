<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\CreateProductRequest;
use App\Http\Requests\Inventory\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\InventoryService;
use App\Services\ProductService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected ProductService $productService,
        protected TenantContext $tenantContext
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $business = $this->tenantContext->getCurrentBusiness();
        $summary = $this->inventoryService->getSummary($business);

        $filters = [
            'search' => $request->query('search'),
            'category_id' => $request->query('category_id'),
            'stock_status' => $request->query('stock_status'),
            'status' => $request->query('status', 'active'),
            'sort' => $request->query('sort', 'recent'),
        ];

        $products = $this->inventoryService->getFilteredProducts($business, $filters, 25);
        $categories = Category::where('business_id', $business->id)->get();

        return view('app.inventory.index', [
            'business' => $business,
            'summary' => $summary,
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
            'user' => Auth::user(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        $business = $this->tenantContext->getCurrentBusiness();
        $categories = Category::where('business_id', $business->id)->get();

        return view('app.inventory.create', [
            'business' => $business,
            'categories' => $categories,
            'user' => Auth::user(),
        ]);
    }

    public function store(CreateProductRequest $request): RedirectResponse
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $product = $this->productService->createProduct($request->validated(), $business, Auth::user());

        return redirect()->route('app.inventory.show', $product->id)
            ->with('success', 'Product "' . $product->name . '" added successfully.');
    }

    public function show(int $id): View
    {
        $business = $this->tenantContext->getCurrentBusiness();

        // Support showing archived products as well
        $product = Product::withTrashed()
            ->where('business_id', $business->id)
            ->with(['category', 'stockMovements.user'])
            ->findOrFail($id);

        $this->authorize('view', $product);

        return view('app.inventory.show', [
            'product' => $product,
            'business' => $business,
            'user' => Auth::user(),
        ]);
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $business = $this->tenantContext->getCurrentBusiness();
        $categories = Category::where('business_id', $business->id)->get();

        return view('app.inventory.edit', [
            'product' => $product,
            'business' => $business,
            'categories' => $categories,
            'user' => Auth::user(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->productService->updateProduct($product, $request->validated(), Auth::user());

        return redirect()->route('app.inventory.show', $product->id)
            ->with('success', 'Product details updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->productService->archiveProduct($product, Auth::user());

        return redirect()->route('app.inventory.index')
            ->with('success', 'Product "' . $product->name . '" has been archived.');
    }

    public function restore(int $id): RedirectResponse
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $product = Product::onlyTrashed()
            ->where('business_id', $business->id)
            ->findOrFail($id);

        $this->authorize('restore', $product);

        $this->productService->restoreProduct($product, Auth::user());

        return redirect()->route('app.inventory.show', $product->id)
            ->with('success', 'Product "' . $product->name . '" restored successfully.');
    }
}
