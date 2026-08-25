<?php

namespace Tests\Feature\SaaS;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PlanAndFeatureSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $cashier;
    protected User $platformAdmin;
    protected Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolesAndPermissionsSeeder::class,
            PlanAndFeatureSeeder::class,
        ]);

        $this->owner = User::create([
            'name' => 'Store Owner',
            'email' => 'owner@business.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'is_platform_admin' => false,
        ]);

        $this->cashier = User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@business.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'is_platform_admin' => false,
        ]);

        $this->platformAdmin = User::create([
            'name' => 'Platform Superadmin',
            'email' => 'superadmin@orvikstore.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'is_platform_admin' => true,
        ]);

        $this->business = Business::create([
            'name' => 'Lahore Retail Stall',
            'owner_id' => $this->owner->id,
            'currency' => 'PKR',
            'timezone' => 'Asia/Karachi',
            'status' => 'active',
        ]);

        $ownerRole = Role::where('name', Role::OWNER)->first();
        $cashierRole = Role::where('name', Role::CASHIER)->first();

        BusinessUser::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'role_id' => $ownerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        BusinessUser::create([
            'business_id' => $this->business->id,
            'user_id' => $this->cashier->id,
            'role_id' => $cashierRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    public function test_normal_business_users_are_forbidden_from_platform_admin(): void
    {
        // 1. Unauthenticated request
        $response = $this->get('/platform');
        $response->assertRedirect('/login');

        // 2. Business Owner request -> 403
        $response = $this->actingAs($this->owner)->get('/platform');
        $response->assertStatus(403);

        $response = $this->actingAs($this->owner)->get('/platform/businesses');
        $response->assertStatus(403);

        $response = $this->actingAs($this->owner)->get('/platform/plans');
        $response->assertStatus(403);

        // 3. Cashier request -> 403
        $response = $this->actingAs($this->cashier)->get('/platform');
        $response->assertStatus(403);
    }

    public function test_platform_admin_can_access_all_platform_management_routes(): void
    {
        $this->actingAs($this->platformAdmin);

        // Dashboard
        $response = $this->get('/platform');
        $response->assertStatus(200);
        $response->assertSee('SaaS Overview Dashboard');

        // Businesses list
        $response = $this->get('/platform/businesses');
        $response->assertStatus(200);
        $response->assertSee('Lahore Retail Stall');

        // Plans management
        $response = $this->get('/platform/plans');
        $response->assertStatus(200);
        $response->assertSee('Starter');

        // SaaS Analytics
        $response = $this->get('/platform/analytics');
        $response->assertStatus(200);
        $response->assertSee('SaaS Growth');

        // Platform Settings
        $response = $this->get('/platform/settings');
        $response->assertStatus(200);
        $response->assertSee('Default Trial Duration');
    }
}
