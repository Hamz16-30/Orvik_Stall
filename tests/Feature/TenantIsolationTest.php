<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_tenant_a_cannot_access_or_modify_tenant_b_resources(): void
    {
        $ownerRole = Role::where('name', Role::OWNER)->first();

        // 1. Create Business A with User A
        $userA = User::create([
            'name' => 'Owner A',
            'email' => 'owner_a@orvikstore.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
        $businessA = Business::create([
            'name' => 'Stall A',
            'owner_id' => $userA->id,
            'currency' => 'PKR',
        ]);
        BusinessUser::create([
            'business_id' => $businessA->id,
            'user_id' => $userA->id,
            'role_id' => $ownerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // 2. Create Business B with User B and Invitation in B
        $userB = User::create([
            'name' => 'Owner B',
            'email' => 'owner_b@orvikstore.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
        $businessB = Business::create([
            'name' => 'Stall B',
            'owner_id' => $userB->id,
            'currency' => 'USD',
        ]);
        BusinessUser::create([
            'business_id' => $businessB->id,
            'user_id' => $userB->id,
            'role_id' => $ownerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $cashierRole = Role::where('name', Role::CASHIER)->first();
        $invitationB = Invitation::create([
            'business_id' => $businessB->id,
            'email' => 'employee_b@orvikstore.com',
            'role_id' => $cashierRole->id,
            'token' => 'secret-invitation-token-for-business-b',
            'expires_at' => now()->addDays(7),
            'invited_by' => $userB->id,
        ]);

        // 3. Authenticate as User A
        $this->actingAs($userA);

        // User A views staff page — only Business A records should be visible
        $response = $this->get('/app/settings/staff');
        $response->assertOk();
        $response->assertSee('Owner A');
        $response->assertDontSee('Owner B');
        $response->assertDontSee('employee_b@orvikstore.com');

        // User A attempts to cancel invitation belonging to Business B
        $cancelResponse = $this->delete('/app/settings/invitations/' . $invitationB->id);
        $cancelResponse->assertStatus(403);

        // Ensure invitation in Business B was not deleted
        $this->assertDatabaseHas('invitations', ['id' => $invitationB->id]);
    }
}
