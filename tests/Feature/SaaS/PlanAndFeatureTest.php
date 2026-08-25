<?php

namespace Tests\Feature\SaaS;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SaaS\FeatureService;
use Database\Seeders\PlanAndFeatureSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanAndFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Business $business;
    protected FeatureService $featureService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolesAndPermissionsSeeder::class,
            PlanAndFeatureSeeder::class,
        ]);

        $this->featureService = app(FeatureService::class);

        $this->owner = User::create([
            'name' => 'Stall Owner',
            'email' => 'owner@stall.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $this->business = Business::create([
            'name' => 'Al-Madina Store',
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

    public function test_plans_and_features_are_database_driven(): void
    {
        $starterPlan = Plan::where('slug', Plan::SLUG_STARTER)->first();
        $this->assertNotNull($starterPlan);
        $this->assertEquals(500, $starterPlan->getFeatureLimit('products'));
        $this->assertEquals(3, $starterPlan->getFeatureLimit('users'));
        $this->assertTrue($starterPlan->hasFeature('excel_export'));

        $freePlan = Plan::where('slug', Plan::SLUG_FREE)->first();
        $this->assertNotNull($freePlan);
        $this->assertEquals(50, $freePlan->getFeatureLimit('products'));
        $this->assertFalse($freePlan->hasFeature('excel_export'));
    }

    public function test_business_feature_gate_resolution(): void
    {
        $starterPlan = Plan::where('slug', Plan::SLUG_STARTER)->first();

        Subscription::create([
            'business_id' => $this->business->id,
            'plan_id' => $starterPlan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertTrue($this->featureService->canAccessFeature($this->business, 'excel_export'));
        $this->assertFalse($this->featureService->canAccessFeature($this->business, 'advanced_reports'));
        $this->assertEquals(500, $this->featureService->getFeatureLimit($this->business, 'products'));
    }

    public function test_public_pricing_page_renders_dynamic_database_plans(): void
    {
        $response = $this->get('/pricing');

        $response->assertStatus(200);
        $response->assertSee('Free');
        $response->assertSee('Starter');
        $response->assertSee('Growth');
        $response->assertSee('Pro');
        $response->assertSee('1,999');
    }

    public function test_pricing_comparison_page_renders_feature_matrix(): void
    {
        $response = $this->get('/pricing/compare');

        $response->assertStatus(200);
        $response->assertSee('Compare All Features');
        $response->assertSee('Products Limit');
        $response->assertSee('Excel (.xls) Exports');
    }
}
