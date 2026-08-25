<?php

namespace App\Http\Controllers;

use App\Services\SaaS\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {}

    public function handle(Request $request, string $provider): JsonResponse
    {
        $signature = (string) ($request->header('X-Webhook-Signature') ?? $request->header('Stripe-Signature') ?? '');
        $rawPayload = (string) $request->getContent();

        $result = $this->billingService->handleWebhook($provider, $rawPayload, $signature);

        return response()->json($result, $result['code'] ?? 200);
    }
}
