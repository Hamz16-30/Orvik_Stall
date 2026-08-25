<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\CreateCategoryRequest;
use App\Http\Requests\Inventory\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\AuditLogger;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Category::class);

        $business = $this->tenantContext->getCurrentBusiness();
        $categories = Category::where('business_id', $business->id)
            ->withCount(['products' => function ($query) {
                $query->withoutTrashed();
            }])
            ->get();

        return view('app.inventory.categories.index', [
            'business' => $business,
            'categories' => $categories,
            'user' => Auth::user(),
        ]);
    }

    public function store(CreateCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $business = $this->tenantContext->getCurrentBusiness();

        $category = Category::create([
            'business_id' => $business->id,
            'name' => $request->name,
            'description' => $request->description,
            'status' => 'active',
        ]);

        AuditLogger::log(
            action: 'category.created',
            entityType: Category::class,
            entityId: $category->id,
            newValues: ['name' => $category->name],
            businessId: $business->id,
            userId: Auth::id()
        );

        return redirect()->route('app.inventory.categories.index')
            ->with('success', 'Category "' . $category->name . '" created successfully.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $oldValues = $category->only(['name', 'description']);
        $category->update($request->validated());

        AuditLogger::log(
            action: 'category.updated',
            entityType: Category::class,
            entityId: $category->id,
            oldValues: $oldValues,
            newValues: $request->validated(),
            businessId: $category->business_id,
            userId: Auth::id()
        );

        return redirect()->route('app.inventory.categories.index')
            ->with('success', 'Category "' . $category->name . '" updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        AuditLogger::log(
            action: 'category.archived',
            entityType: Category::class,
            entityId: $category->id,
            oldValues: ['name' => $category->name],
            businessId: $category->business_id,
            userId: Auth::id()
        );

        return redirect()->route('app.inventory.categories.index')
            ->with('success', 'Category "' . $category->name . '" archived.');
    }

    public function restore(int $id): RedirectResponse
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $category = Category::onlyTrashed()
            ->where('business_id', $business->id)
            ->findOrFail($id);

        $this->authorize('restore', $category);

        $category->restore();

        AuditLogger::log(
            action: 'category.restored',
            entityType: Category::class,
            entityId: $category->id,
            newValues: ['name' => $category->name],
            businessId: $business->id,
            userId: Auth::id()
        );

        return redirect()->route('app.inventory.categories.index')
            ->with('success', 'Category "' . $category->name . '" restored.');
    }
}
