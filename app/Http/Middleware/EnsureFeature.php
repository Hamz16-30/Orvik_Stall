<?php

namespace App\Http\Middleware;

use App\Services\SaaS\FeatureService;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeature
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected FeatureService $featureService
    ) {}

    /**
     * Handle incoming request.
     */
    public function handle(Request $request, Closure $next, string $featureSlug): Response
    {
        $business = $this->tenantContext->getCurrentBusiness();

        if (!$business || !$this->featureService->canAccessFeature($business, $featureSlug)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Your current plan does not include the '{$featureSlug}' feature. Please upgrade your plan.",
                    'feature' => $featureSlug,
                    'upgrade_url' => route('settings.plan'),
                ], 403);
            }

            return redirect()->route('settings.plan')->withErrors([
                'plan' => "The '{$featureSlug}' feature requires an upgraded subscription plan.",
            ]);
        }

        return $next($request);
    }
}
