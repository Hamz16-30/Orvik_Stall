<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_registration_creates_user_business_and_owner_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Tariq Mehmood',
            'business_name' => 'Stall Alpha Retail',
            'email' => 'tariq@orvikstore.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/app/dashboard');

        $this->assertAuthenticated();

        $user = User::where('email', 'tariq@orvikstore.com')->first();
        $this->assertNotNull($user);

        $business = Business::where('name', 'Stall Alpha Retail')->first();
        $this->assertNotNull($business);
        $this->assertEquals($user->id, $business->owner_id);

        $this->assertTrue($user->isOwner());
        $this->assertTrue($user->hasPermissionTo('settings.update'));
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::create([
            'name' => 'Test Operator',
            'email' => 'operator@orvikstore.com',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]);

        $business = Business::create([
            'name' => 'Demo Stall',
            'owner_id' => $user->id,
            'currency' => 'PKR',
        ]);

        $ownerRole = Role::where('name', Role::OWNER)->first();
        $user->businesses()->attach($business->id, [
            'role_id' => $ownerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'operator@orvikstore.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/app/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::create([
            'name' => 'Test Operator',
            'email' => 'operator@orvikstore.com',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'operator@orvikstore.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get('/app/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_user_can_logout_and_session_is_invalidated(): void
    {
        $user = User::create([
            'name' => 'Test Operator',
            'email' => 'operator@orvikstore.com',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]);

        $business = Business::create([
            'name' => 'Demo Stall',
            'owner_id' => $user->id,
        ]);

        $ownerRole = Role::where('name', Role::OWNER)->first();
        $user->businesses()->attach($business->id, [
            'role_id' => $ownerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->post('/app/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
