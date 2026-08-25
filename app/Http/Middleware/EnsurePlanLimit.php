<?php

namespace App\Http\Middleware;

use App\Services\SaaS\UsageService;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanLimit
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected UsageService $usageService
    ) {}

    /**
     * Handle incoming request.
     */
    public function handle(Request $request, Closure $next, string $limitType): Response
    {
        $business = $this->tenantContext->getCurrentBusiness();
        if (!$business) {
            return $next($request);
        }

        $canProceed = match ($limitType) {
            'products' => $this->usageService->canCreateProduct($business),
            'users' => $this->usageService->canCreateUser($business),
            'sales' => $this->usageService->canCompleteSale($business),
            'customers' => $this->usageService->canCreateCustomer($business),
            'suppliers' => $this->usageService->canCreateSupplier($business),
            default => true,
        };

        if (!$canProceed) {
            $msg = "You have reached your plan's maximum allowed limit for {$limitType}. Upgrade your plan to continue adding {$limitType}.";

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $msg,
                    'limit_type' => $limitType,
                    'upgrade_url' => route('settings.plan'),
                ], 422);
            }

            return back()->withInput()->withErrors([
                'plan_limit' => $msg,
            ]);
        }

        return $next($request);
    }
}
