<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InventoryTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_tenant_a_cannot_view_modify_or_adjust_tenant_b_products(): void
    {
        $ownerRole = Role::where('name', Role::OWNER)->first();

        // 1. Business A setup
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
        $productA = Product::create([
            'business_id' => $businessA->id,
            'name' => 'Stall A Water Bottle',
            'selling_price' => 50.00,
            'stock_quantity' => 100.000,
        ]);

        // 2. Business B setup
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
        $productB = Product::create([
            'business_id' => $businessB->id,
            'name' => 'Stall B Secret Diamond Product',
            'selling_price' => 999.00,
            'stock_quantity' => 5.000,
        ]);

        // 3. Authenticate as User A
        $this->actingAs($userA);

        // List test: Product B is not visible in Product A's inventory list
        $indexResponse = $this->get('/app/inventory');
        $indexResponse->assertOk();
        $indexResponse->assertSee('Stall A Water Bottle');
        $indexResponse->assertDontSee('Stall B Secret Diamond Product');

        // Direct show attempt on Product B is rejected (404/403 due to tenant scope)
        $showResponse = $this->get('/app/inventory/' . $productB->id);
        $this->assertTrue(in_array($showResponse->status(), [403, 404]));

        // Direct stock adjust attempt on Product B is rejected
        $adjustResponse = $this->post('/app/inventory/' . $productB->id . '/stock-adjustment', [
            'type' => 'ADD',
            'quantity' => 50.000,
        ]);
        $this->assertTrue(in_array($adjustResponse->status(), [403, 404]));

        // Direct archive attempt on Product B is rejected
        $deleteResponse = $this->delete('/app/inventory/' . $productB->id);
        $this->assertTrue(in_array($deleteResponse->status(), [403, 404]));

        // Ensure Product B was never modified
        $productB->refresh();
        $this->assertEquals(5.000, (float)$productB->stock_quantity);
        $this->assertFalse($productB->trashed());
    }
}
