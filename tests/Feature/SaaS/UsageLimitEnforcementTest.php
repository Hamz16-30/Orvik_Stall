<?php

namespace Tests\Feature\SaaS;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Category;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ProductService;
use App\Services\SaaS\SubscriptionService;
use App\Services\SaaS\UsageService;
use App\Services\SalesService;
use Database\Seeders\PlanAndFeatureSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UsageLimitEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Business $business;
    protected UsageService $usageService;
    protected ProductService $productService;
    protected SalesService $salesService;
    protected SubscriptionService $subscriptionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolesAndPermissionsSeeder::class,
            PlanAndFeatureSeeder::class,
        ]);

        $this->usageService = app(UsageService::class);
        $this->productService = app(ProductService::class);
        $this->salesService = app(SalesService::class);
        $this->subscriptionService = app(SubscriptionService::class);

        $this->owner = User::create([
            'name' => 'Store Owner',
            'email' => 'owner@retail.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $this->business = Business::create([
            'name' => 'Fast Mart',
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

    public function test_product_limit_is_strictly_enforced_server_side(): void
    {
        // Create custom tier with product limit 3
        $customPlan = Plan::create([
            'name' => 'Micro Tier',
            'slug' => 'micro-tier',
            'price' => 500,
            'billing_interval' => 'monthly',
            'currency' => 'PKR',
            'is_active' => true,
        ]);

        $prodFeature = Feature::where('slug', 'products')->first();
        PlanFeature::create([
            'plan_id' => $customPlan->id,
            'feature_id' => $prodFeature->id,
            'enabled' => true,
            'limit_value' => 3,
        ]);

        Subscription::create([
            'business_id' => $this->business->id,
            'plan_id' => $customPlan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // Create 3 products
        for ($i = 1; $i <= 3; $i++) {
            $this->productService->createProduct([
                'name' => "Product {$i}",
                'selling_price' => 100 * $i,
                'opening_stock' => 10,
            ], $this->business, $this->owner);
        }

        $this->assertEquals(3, Product::where('business_id', $this->business->id)->count());
        $this->assertFalse($this->usageService->canCreateProduct($this->business));

        // Attempting to create 4th product must throw ValidationException
        $this->expectException(ValidationException::class);
        $this->productService->createProduct([
            'name' => 'Forbidden 4th Product',
            'selling_price' => 200,
        ], $this->business, $this->owner);
    }

    public function test_monthly_sales_quota_is_strictly_enforced_server_side(): void
    {
        // Create custom tier with 2 sales limit
        $customPlan = Plan::create([
            'name' => 'Demo Plan',
            'slug' => 'demo-plan',
            'price' => 0,
            'billing_interval' => 'monthly',
            'currency' => 'PKR',
            'is_active' => true,
        ]);

        $salesFeature = Feature::where('slug', 'monthly_sales')->first();
        PlanFeature::create([
            'plan_id' => $customPlan->id,
            'feature_id' => $salesFeature->id,
            'enabled' => true,
            'limit_value' => 2,
        ]);

        Subscription::create([
            'business_id' => $this->business->id,
            'plan_id' => $customPlan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Test Item',
            'selling_price' => 50,
            'stock_quantity' => 100,
            'status' => 'active',
        ]);

        // Complete 2 sales
        for ($i = 1; $i <= 2; $i++) {
            $this->salesService->completeSale([
                'payment_method' => 'cash',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 50],
                ],
            ], $this->business, $this->owner);
        }

        $this->assertEquals(2, Sale::where('business_id', $this->business->id)->count());
        $this->assertFalse($this->usageService->canCompleteSale($this->business));

        // 3rd sale must be blocked
        $this->expectException(ValidationException::class);
        $this->salesService->completeSale([
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 50],
            ],
        ], $this->business, $this->owner);
    }

    public function test_safe_downgrade_preserves_existing_data_while_blocking_new_creations(): void
    {
        // Business starts on Starter (500 products limit) and creates 5 products
        $starter = Plan::where('slug', Plan::SLUG_STARTER)->first();
        Subscription::create([
            'business_id' => $this->business->id,
            'plan_id' => $starter->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        for ($i = 1; $i <= 5; $i++) {
            Product::create([
                'business_id' => $this->business->id,
                'name' => "Existing Item {$i}",
                'selling_price' => 50,
                'stock_quantity' => 10,
                'status' => 'active',
            ]);
        }

        $this->assertEquals(5, Product::where('business_id', $this->business->id)->count());

        // Create micro plan with 3 product limit and downgrade business
        $microPlan = Plan::create([
            'name' => 'Mini Plan',
            'slug' => 'mini-plan',
            'price' => 100,
            'billing_interval' => 'monthly',
            'currency' => 'PKR',
            'is_active' => true,
        ]);

        $prodFeature = Feature::where('slug', 'products')->first();
        PlanFeature::create([
            'plan_id' => $microPlan->id,
            'feature_id' => $prodFeature->id,
            'enabled' => true,
            'limit_value' => 3,
        ]);

        $result = $this->subscriptionService->changePlan($this->business, $microPlan);

        // Verify downgrade warning generated
        $this->assertNotEmpty($result['warnings']);

        // Verify all 5 existing products remain untouched
        $this->assertEquals(5, Product::where('business_id', $this->business->id)->count());

        // Verify new creation is blocked
        $this->assertFalse($this->usageService->canCreateProduct($this->business));
    }
}
