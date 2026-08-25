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

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_owner_can_invite_staff_and_staff_can_accept(): void
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

        $this->actingAs($owner);

        // 1. Send Invitation
        $response = $this->post('/app/settings/staff/invite', [
            'email' => 'newstaff@orvikstore.com',
            'role_id' => $cashierRole->id,
        ]);

        $response->assertSessionHasNoErrors();

        $invitation = Invitation::where('email', 'newstaff@orvikstore.com')->first();
        $this->assertNotNull($invitation);
        $this->assertTrue($invitation->isValid());

        // 2. Accept Invitation as Guest
        $this->post('/app/logout');

        $acceptResponse = $this->post('/invitations/accept/' . $invitation->token, [
            'name' => 'New Staff Member',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $acceptResponse->assertRedirect('/app/dashboard');
        $this->assertAuthenticated();

        $newUser = User::where('email', 'newstaff@orvikstore.com')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->isCashier());

        // 3. Verify Invitation is now marked as accepted and cannot be reused
        $invitation->refresh();
        $this->assertTrue($invitation->isAccepted());

        // Logout new user so we test guest trying to reuse the token
        $this->post('/app/logout');

        $reuseResponse = $this->post('/invitations/accept/' . $invitation->token, [
            'name' => 'Another Person',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $reuseResponse->assertStatus(403);
    }

    public function test_expired_invitation_is_rejected(): void
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

        $invitation = Invitation::create([
            'business_id' => $business->id,
            'email' => 'expired@orvikstore.com',
            'role_id' => $cashierRole->id,
            'token' => 'expired-token-12345',
            'expires_at' => now()->subDay(), // Expired yesterday
            'invited_by' => $owner->id,
        ]);

        $response = $this->post('/invitations/accept/' . $invitation->token, [
            'name' => 'Expired User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(403);
    }
}
