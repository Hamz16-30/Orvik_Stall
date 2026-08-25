<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SaaS\BillingService;
use App\Services\SaaS\SubscriptionService;
use App\Services\SaaS\UsageService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected UsageService $usageService,
        protected SubscriptionService $subscriptionService,
        protected BillingService $billingService
    ) {}

    /**
     * View Plan & Resource Usage Dashboard.
     */
    public function plan(): View
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $subscription = $business->currentSubscription();
        $plan = $business->plan();
        $usage = $this->usageService->getAllUsage($business);
        $availablePlans = Plan::where('is_active', true)->where('is_public', true)->orderBy('sort_order')->get();

        return view('app.settings.plan', [
            'business' => $business,
            'subscription' => $subscription,
            'plan' => $plan,
            'usage' => $usage,
            'availablePlans' => $availablePlans,
            'user' => Auth::user(),
        ]);
    }

    /**
     * View Billing & Invoices Management.
     */
    public function billing(): View
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $subscription = $business->currentSubscription();
        $plan = $business->plan();
        $invoices = $business->invoices()->latest()->paginate(10);
        $availablePlans = Plan::where('is_active', true)->where('is_public', true)->orderBy('sort_order')->get();

        return view('app.settings.billing', [
            'business' => $business,
            'subscription' => $subscription,
            'plan' => $plan,
            'invoices' => $invoices,
            'availablePlans' => $availablePlans,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Handle Plan Upgrade or Downgrade.
     */
    public function upgrade(Request $request): RedirectResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_interval' => 'required|in:monthly,yearly',
        ]);

        $business = $this->tenantContext->getCurrentBusiness();
        $newPlan = Plan::findOrFail($request->plan_id);

        $result = $this->subscriptionService->changePlan($business, $newPlan, $request->billing_interval, 'manual');

        // Create invoice if paid plan
        if (!$newPlan->isFree()) {
            $amount = $request->billing_interval === 'yearly' && $newPlan->yearly_price ? $newPlan->yearly_price : $newPlan->price;
            $this->billingService->createInvoice($business, $result['subscription'], (float) $amount, $newPlan->currency);
        }

        $msg = "Subscription successfully updated to {$newPlan->name} Plan!";
        if (!empty($result['warnings'])) {
            $msg .= ' Note: ' . implode(' ', $result['warnings']);
        }

        return redirect()->route('app.settings.plan')->with('success', $msg);
    }

    /**
     * Cancel Subscription.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $this->subscriptionService->cancelSubscription($business, true);

        return redirect()->route('app.settings.billing')->with('success', 'Your subscription will be cancelled at the end of the current billing cycle. Your data remains fully retained.');
    }

    /**
     * Reactivate Subscription.
     */
    public function reactivate(): RedirectResponse
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $this->subscriptionService->reactivateSubscription($business);

        return redirect()->route('app.settings.billing')->with('success', 'Your subscription has been successfully reactivated!');
    }
}
