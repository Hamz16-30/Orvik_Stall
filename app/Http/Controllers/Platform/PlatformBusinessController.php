<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SaaS\SubscriptionService;
use App\Services\SaaS\UsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlatformBusinessController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected UsageService $usageService
    ) {}

    public function index(Request $request): View
    {
        $query = Business::with(['owner', 'subscriptions.plan', 'products', 'sales']);

        // Search by business name, owner name, owner email, or ID
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id', $search)
                    ->orWhereHas('owner', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status (all, active, trialing, paid, suspended, cancelled)
        if ($status = $request->get('status')) {
            if ($status === 'active') {
                $query->where('status', 'active');
            } elseif ($status === 'suspended') {
                $query->where('status', 'suspended');
            } elseif ($status === 'trialing') {
                $query->whereHas('subscriptions', fn($sq) => $sq->where('status', Subscription::STATUS_TRIALING));
            } elseif ($status === 'paid') {
                $query->whereHas('subscriptions', fn($sq) => $sq->where('status', Subscription::STATUS_ACTIVE)->whereHas('plan', fn($pq) => $pq->where('price', '>', 0)));
            } elseif ($status === 'cancelled') {
                $query->whereHas('subscriptions', fn($sq) => $sq->where('status', Subscription::STATUS_CANCELLED));
            }
        }

        $businesses = $query->latest()->paginate(15)->withQueryString();
        $plans = Plan::where('is_active', true)->get();

        return view('platform.businesses.index', [
            'businesses' => $businesses,
            'plans' => $plans,
            'filters' => $request->only(['search', 'status']),
            'user' => Auth::user(),
        ]);
    }

    public function show(Business $business): View
    {
        $business->load(['owner', 'subscriptions.plan.features', 'suspensions.suspendedBy', 'suspensions.unsuspendedBy']);
        $usage = $this->usageService->getAllUsage($business);
        $plans = Plan::where('is_active', true)->get();

        return view('platform.businesses.show', [
            'business' => $business,
            'usage' => $usage,
            'plans' => $plans,
            'currentSubscription' => $business->currentSubscription(),
            'user' => Auth::user(),
        ]);
    }

    public function suspend(Request $request, Business $business): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->subscriptionService->suspendBusiness($business, $request->reason, Auth::user());

        return back()->with('success', "Business '{$business->name}' has been suspended.");
    }

    public function unsuspend(Business $business): RedirectResponse
    {
        $this->subscriptionService->unsuspendBusiness($business, Auth::user());

        return back()->with('success', "Business '{$business->name}' has been reactivated.");
    }

    public function changePlan(Request $request, Business $business): RedirectResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_interval' => 'required|in:monthly,yearly',
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $result = $this->subscriptionService->changePlan($business, $plan, $request->billing_interval, 'manual_admin');

        $msg = "Plan for '{$business->name}' changed to {$plan->name}.";
        if (!empty($result['warnings'])) {
            $msg .= ' ' . implode(' ', $result['warnings']);
        }

        return back()->with('success', $msg);
    }
}
