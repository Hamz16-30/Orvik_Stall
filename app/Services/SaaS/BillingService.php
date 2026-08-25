<?php

namespace App\Services\SaaS;

use App\Models\BillingEvent;
use App\Models\BillingInvoice;
use App\Models\Business;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Support\Str;

interface BillingProviderInterface
{
    public function getName(): string;
    public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool;
    public function processWebhook(array $payload): array;
}

class ManualBillingProvider implements BillingProviderInterface
{
    public function getName(): string
    {
        return 'manual';
    }

    public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        return true;
    }

    public function processWebhook(array $payload): array
    {
        return [
            'event_id' => $payload['event_id'] ?? (string) Str::uuid(),
            'event_type' => $payload['event_type'] ?? 'manual.invoice.paid',
            'business_id' => $payload['business_id'] ?? null,
            'amount' => $payload['amount'] ?? 0.0,
            'status' => 'success',
        ];
    }
}

class TestBillingProvider implements BillingProviderInterface
{
    public function getName(): string
    {
        return 'test';
    }

    public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    public function processWebhook(array $payload): array
    {
        return [
            'event_id' => $payload['id'] ?? (string) Str::uuid(),
            'event_type' => $payload['type'] ?? 'payment_intent.succeeded',
            'business_id' => $payload['data']['business_id'] ?? null,
            'amount' => $payload['data']['amount'] ?? 0.0,
            'status' => 'success',
        ];
    }
}

class BillingService
{
    protected array $providers = [];

    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {
        $this->registerProvider(new ManualBillingProvider());
        $this->registerProvider(new TestBillingProvider());
    }

    public function registerProvider(BillingProviderInterface $provider): void
    {
        $this->providers[$provider->getName()] = $provider;
    }

    public function getProvider(string $name): ?BillingProviderInterface
    {
        return $this->providers[$name] ?? null;
    }

    /**
     * Create a SaaS Billing Invoice.
     */
    public function createInvoice(Business $business, Subscription $subscription, float $amount, string $currency = 'PKR', string $status = 'paid'): BillingInvoice
    {
        $invoiceNumber = 'INV-' . strtoupper(Str::random(4)) . '-' . now()->format('YmdHis');

        return BillingInvoice::create([
            'business_id' => $business->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => $invoiceNumber,
            'amount' => $amount,
            'currency' => $currency,
            'status' => $status,
            'issued_at' => Carbon::now(),
            'paid_at' => $status === 'paid' ? Carbon::now() : null,
        ]);
    }

    /**
     * Handle incoming provider webhook with signature check and idempotency.
     */
    public function handleWebhook(string $providerName, string $rawPayload, string $signature, ?string $secret = null): array
    {
        $provider = $this->getProvider($providerName);
        if (!$provider) {
            return ['status' => 'error', 'message' => "Unknown provider: {$providerName}", 'code' => 400];
        }

        $secret = $secret ?? config("services.{$providerName}.webhook_secret", 'test_secret_key');
        if (!$provider->verifyWebhookSignature($rawPayload, $signature, $secret)) {
            return ['status' => 'error', 'message' => 'Invalid webhook signature.', 'code' => 401];
        }

        $payloadData = json_decode($rawPayload, true) ?: [];
        $processed = $provider->processWebhook($payloadData);
        $eventId = $processed['event_id'];

        // Idempotency check: has this event been processed before?
        $existingEvent = BillingEvent::where('provider', $providerName)
            ->where('event_id', $eventId)
            ->first();

        if ($existingEvent) {
            return [
                'status' => 'ignored',
                'message' => 'Event already processed (idempotent duplicate).',
                'event_id' => $eventId,
                'code' => 200,
            ];
        }

        // Store billing event record
        BillingEvent::create([
            'provider' => $providerName,
            'event_id' => $eventId,
            'event_type' => $processed['event_type'],
            'payload_hash' => hash('sha256', $rawPayload),
            'payload' => $payloadData,
            'processed_at' => Carbon::now(),
            'status' => 'processed',
        ]);

        return [
            'status' => 'success',
            'message' => 'Webhook processed successfully.',
            'event_id' => $eventId,
            'code' => 200,
        ];
    }
}
