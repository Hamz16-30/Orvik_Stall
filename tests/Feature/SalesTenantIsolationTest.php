<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SalesTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_tenant_a_cannot_sell_view_or_void_tenant_b_sales(): void
    {
        $ownerRole = Role::where('name', Role::OWNER)->first();

        // 1. Business A
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

        // 2. Business B
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
            'name' => 'Product B Secret Item',
            'selling_price' => 500.00,
            'stock_quantity' => 10.000,
        ]);
        $saleB = Sale::create([
            'business_id' => $businessB->id,
            'user_id' => $userB->id,
            'invoice_number' => 'INV-000001',
            'subtotal' => 500.00,
            'total_amount' => 500.00,
            'payment_method' => 'cash',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // 3. User A attempts to sell Product B belonging to Business B
        $this->actingAs($userA);

        $sellResponse = $this->postJson('/app/sales/complete', [
            'items' => [
                ['product_id' => $productB->id, 'quantity' => 1],
            ],
            'payment_method' => 'cash',
        ]);
        $sellResponse->assertStatus(422);

        // User A attempts to view Sale B
        $viewResponse = $this->get('/app/sales/' . $saleB->id);
        $this->assertTrue(in_array($viewResponse->status(), [403, 404]));

        // User A attempts to view Receipt for Sale B
        $receiptResponse = $this->get('/app/sales/' . $saleB->id . '/receipt');
        $this->assertTrue(in_array($receiptResponse->status(), [403, 404]));

        // User A attempts to void Sale B
        $voidResponse = $this->post('/app/sales/' . $saleB->id . '/void', [
            'reason' => 'Unauthorized void attempt',
        ]);
        $this->assertTrue(in_array($voidResponse->status(), [403, 404]));
    }
}
