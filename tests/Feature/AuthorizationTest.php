<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_cashier_cannot_access_staff_or_role_management(): void
    {
        $ownerRole = Role::where('name', Role::OWNER)->first();
        $cashierRole = Role::where('name', Role::CASHIER)->first();

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@orvikstore.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $business = Business::create([
            'name' => 'Test Stall',
            'owner_id' => $owner->id,
        ]);

        BusinessUser::create([
            'business_id' => $business->id,
            'user_id' => $owner->id,
            'role_id' => $ownerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $cashier = User::create([
            'name' => 'Cashier Operator',
            'email' => 'cashier@orvikstore.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        BusinessUser::create([
            'business_id' => $business->id,
            'user_id' => $cashier->id,
            'role_id' => $cashierRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // Authenticate as Cashier
        $this->actingAs($cashier);

        // Attempt to access staff management
        $staffResponse = $this->get('/app/settings/staff');
        $staffResponse->assertStatus(403);

        // Attempt to access role management
        $rolesResponse = $this->get('/app/settings/roles');
        $rolesResponse->assertStatus(403);

        // Attempt to access business settings
        $settingsResponse = $this->get('/app/settings/business');
        $settingsResponse->assertStatus(403);
    }

    public function test_manager_cannot_suspend_owner_account(): void
    {
        $ownerRole = Role::where('name', Role::OWNER)->first();
        $managerRole = Role::where('name', Role::MANAGER)->first();

        $owner = User::create([
            'name' => 'Stall Owner',
            'email' => 'owner@orvikstore.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $business = Business::create([
            'name' => 'Test Stall',
            'owner_id' => $owner->id,
        ]);

        BusinessUser::create([
            'business_id' => $business->id,
            'user_id' => $owner->id,
            'role_id' => $ownerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $manager = User::create([
            'name' => 'Stall Manager',
            'email' => 'manager@orvikstore.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        BusinessUser::create([
            'business_id' => $business->id,
            'user_id' => $manager->id,
            'role_id' => $managerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // Authenticate as Manager
        $this->actingAs($manager);

        // Attempt to suspend Owner
        $response = $this->put('/app/settings/staff/' . $owner->id . '/suspend');
        $response->assertStatus(403);

        $ownerMembership = BusinessUser::where('user_id', $owner->id)->first();
        $this->assertEquals('active', $ownerMembership->status);
    }

    public function test_suspended_user_is_logged_out_and_rejected(): void
    {
        $ownerRole = Role::where('name', Role::OWNER)->first();

        $user = User::create([
            'name' => 'Suspended User',
            'email' => 'suspended@orvikstore.com',
            'password' => Hash::make('password123'),
            'status' => 'suspended',
        ]);

        $business = Business::create([
            'name' => 'Test Stall',
            'owner_id' => $user->id,
        ]);

        BusinessUser::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'role_id' => $ownerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/app/dashboard');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
