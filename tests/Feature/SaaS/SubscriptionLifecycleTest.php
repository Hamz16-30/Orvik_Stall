<?php

namespace Tests\Feature\SaaS;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SaaS\FeatureService;
use App\Services\SaaS\SubscriptionService;
use Database\Seeders\PlanAndFeatureSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;
    protected Business $business;
    protected SubscriptionService $subscriptionService;
    protected FeatureService $featureService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolesAndPermissionsSeeder::class,
            PlanAndFeatureSeeder::class,
        ]);

        $this->subscriptionService = app(SubscriptionService::class);
        $this->featureService = app(FeatureService::class);

        $this->owner = User::create([
            'name' => 'Owner User',
            'email' => 'owner@saas.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $this->admin = User::create([
            'name' => 'Platform Admin',
            'email' => 'admin@orvikstore.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'is_platform_admin' => true,
        ]);

        $this->business = Business::create([
            'name' => 'Trial Stall',
            'owner_id' => $this->owner->id,
            'currency' => 'PKR',
            'timezone' => 'Asia/Karachi',
            'status' => 'active',
        ]);

        $ownerRole = Role::where('name', Role::OWNER)->first();
        BusinessUser::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'role_id' => $ownerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    public function test_automatic_trial_provisioning_on_registration(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Vendor',
            'email' => 'vendor@newstall.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_name' => 'Modern Vendor Mart',
        ]);

        $response->assertRedirect('/app/dashboard');

        $vendor = User::where('email', 'vendor@newstall.com')->first();
        $this->assertNotNull($vendor);

        $newBusiness = $vendor->ownedBusinesses()->first();
        $this->assertNotNull($newBusiness);

        $sub = $newBusiness->currentSubscription();
        $this->assertNotNull($sub);
        $this->assertEquals(Subscription::STATUS_TRIALING, $sub->status);
        $this->assertTrue($sub->isTrialing());
        $this->assertEquals(14, $sub->daysLeftInTrial());
    }

    public function test_plan_upgrade_and_cancellation_lifecycle(): void
    {
        $growthPlan = Plan::where('slug', Plan::SLUG_GROWTH)->first();

        // 1. Upgrade to Growth
        $this->actingAs($this->owner);
        $response = $this->post('/app/settings/billing/upgrade', [
            'plan_id' => $growthPlan->id,
            'billing_interval' => 'monthly',
        ]);

        $response->assertRedirect(route('app.settings.plan'));
        $this->assertEquals(Plan::SLUG_GROWTH, $this->business->fresh()->plan()->slug);
        $this->assertTrue($this->business->hasFeature('advanced_reports'));

        // 2. Cancel Subscription (at period end)
        $response = $this->post('/app/settings/billing/cancel');
        $response->assertRedirect(route('app.settings.billing'));

        $sub = $this->business->fresh()->currentSubscription();
        $this->assertTrue($sub->cancel_at_period_end);

        // 3. Reactivate Subscription
        $response = $this->post('/app/settings/billing/reactivate');
        $response->assertRedirect(route('app.settings.billing'));

        $this->assertFalse($this->business->fresh()->currentSubscription()->cancel_at_period_end);
    }

    public function test_business_suspension_and_reactivation(): void
    {
        // Active business has feature access
        $starter = Plan::where('slug', Plan::SLUG_STARTER)->first();
        Subscription::create([
            'business_id' => $this->business->id,
            'plan_id' => $starter->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertTrue($this->featureService->canAccessFeature($this->business, 'excel_export'));

        // Admin suspends business
        $this->subscriptionService->suspendBusiness($this->business, 'Terms of service violation', $this->admin);

        $this->assertTrue($this->business->fresh()->isSuspended());
        $this->assertFalse($this->featureService->canAccessFeature($this->business->fresh(), 'excel_export'));

        // Admin reactivates business
        $this->subscriptionService->unsuspendBusiness($this->business->fresh(), $this->admin);

        $this->assertFalse($this->business->fresh()->isSuspended());
        $this->assertTrue($this->featureService->canAccessFeature($this->business->fresh(), 'excel_export'));
    }
}
