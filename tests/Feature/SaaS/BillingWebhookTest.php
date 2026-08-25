<?php

namespace Tests\Feature\SaaS;

use App\Models\BillingEvent;
use App\Services\SaaS\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billingService = app(BillingService::class);
    }

    public function test_webhook_processes_valid_signature_and_logs_event(): void
    {
        $payload = json_encode([
            'id' => 'evt_test_12345',
            'type' => 'invoice.payment_succeeded',
            'data' => [
                'business_id' => 1,
                'amount' => 1999.00,
            ],
        ]);

        $secret = 'test_secret_key';
        $signature = hash_hmac('sha256', $payload, $secret);

        $response = $this->call(
            'POST',
            '/webhooks/test',
            [],
            [],
            [],
            [
                'HTTP_X_WEBHOOK_SIGNATURE' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'event_id' => 'evt_test_12345',
        ]);

        $this->assertEquals(1, BillingEvent::where('event_id', 'evt_test_12345')->count());
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $payload = json_encode(['id' => 'evt_fake_999']);

        $response = $this->call(
            'POST',
            '/webhooks/test',
            [],
            [],
            [],
            [
                'HTTP_X_WEBHOOK_SIGNATURE' => 'invalid_signature_hash',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $response->assertStatus(401);
        $this->assertEquals(0, BillingEvent::where('event_id', 'evt_fake_999')->count());
    }

    public function test_webhook_idempotency_ignores_duplicate_event(): void
    {
        $payload = json_encode([
            'id' => 'evt_idempotent_100',
            'type' => 'customer.subscription.renewed',
            'data' => ['amount' => 4999.00],
        ]);

        $secret = 'test_secret_key';
        $signature = hash_hmac('sha256', $payload, $secret);

        // 1st request
        $response1 = $this->call(
            'POST',
            '/webhooks/test',
            [],
            [],
            [],
            [
                'HTTP_X_WEBHOOK_SIGNATURE' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );
        $response1->assertStatus(200);
        $response1->assertJson(['status' => 'success']);

        // 2nd request (duplicate event)
        $response2 = $this->call(
            'POST',
            '/webhooks/test',
            [],
            [],
            [],
            [
                'HTTP_X_WEBHOOK_SIGNATURE' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );
        $response2->assertStatus(200);
        $response2->assertJson(['status' => 'ignored']);

        // Assert only 1 record created in database
        $this->assertEquals(1, BillingEvent::where('event_id', 'evt_idempotent_100')->count());
    }
}
