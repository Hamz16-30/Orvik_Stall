<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Models\Sale;
use App\Services\CustomerService;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService,
        protected TenantContext $tenantContext
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        $business = $this->tenantContext->getCurrentBusiness();
        $filters = $request->only(['search', 'status']);
        $customers = $this->customerService->getFiltered($business, $filters, 20);
        $summary = $this->customerService->getSummary($business);

        return view('app.customers.index', [
            'business' => $business,
            'customers' => $customers,
            'summary' => $summary,
            'user' => Auth::user(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);
        $business = $this->tenantContext->getCurrentBusiness();

        return view('app.customers.create', [
            'business' => $business,
            'user' => Auth::user(),
        ]);
    }

    public function store(CustomerRequest $request): JsonResponse|RedirectResponse
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $customer = $this->customerService->createCustomer($request->validated(), $business, Auth::user());

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully.',
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                ],
            ]);
        }

        return redirect()->route('app.customers.show', $customer->id)
            ->with('success', 'Customer "' . $customer->name . '" created successfully.');
    }

    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        $business = $this->tenantContext->getCurrentBusiness();
        $sales = Sale::where('business_id', $business->id)
            ->where('customer_id', $customer->id)
            ->orderByDesc('completed_at')
            ->paginate(15);

        return view('app.customers.show', [
            'customer' => $customer,
            'business' => $business,
            'sales' => $sales,
            'user' => Auth::user(),
        ]);
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);
        $business = $this->tenantContext->getCurrentBusiness();

        return view('app.customers.edit', [
            'customer' => $customer,
            'business' => $business,
            'user' => Auth::user(),
        ]);
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $this->customerService->updateCustomer($customer, $request->validated(), Auth::user());

        return redirect()->route('app.customers.show', $customer->id)
            ->with('success', 'Customer profile updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $this->customerService->archiveCustomer($customer, Auth::user());

        return redirect()->route('app.customers.index')
            ->with('success', 'Customer has been archived.');
    }
}
