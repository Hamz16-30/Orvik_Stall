<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SaleVoidTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected User $owner;
    protected User $cashier;
    protected Product $product;
    protected Sale $sale;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $ownerRole = Role::where('name', Role::OWNER)->first();
        $cashierRole = Role::where('name', Role::CASHIER)->first();

        $this->owner = User::create([
            'name' => 'Store Owner',
            'email' => 'owner@orvikstore.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $this->business = Business::create([
            'name' => 'Main Stall 01',
            'owner_id' => $this->owner->id,
            'currency' => 'PKR',
        ]);

        BusinessUser::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'role_id' => $ownerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->cashier = User::create([
            'name' => 'Cashier Operator',
            'email' => 'cashier@orvikstore.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        BusinessUser::create([
            'business_id' => $this->business->id,
            'user_id' => $this->cashier->id,
            'role_id' => $cashierRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Mineral Water 500ml',
            'selling_price' => 60.00,
            'stock_quantity' => 8.000,
            'track_inventory' => true,
        ]);

        $this->sale = Sale::create([
            'business_id' => $this->business->id,
            'user_id' => $this->cashier->id,
            'invoice_number' => 'INV-000001',
            'subtotal' => 120.00,
            'total_amount' => 120.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        SaleItem::create([
            'sale_id' => $this->sale->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 2.000,
            'unit_price' => 60.00,
            'line_total' => 120.00,
        ]);
    }

    public function test_owner_can_void_sale_and_reverse_stock(): void
    {
        $this->actingAs($this->owner);

        $response = $this->post('/app/sales/' . $this->sale->id . '/void', [
            'reason' => 'Customer returned duplicate purchase',
        ]);

        $response->assertRedirect('/app/sales/' . $this->sale->id);

        $this->sale->refresh();
        $this->assertEquals(Sale::STATUS_VOIDED, $this->sale->status);
        $this->assertEquals('Customer returned duplicate purchase', $this->sale->void_reason);
        $this->assertEquals($this->owner->id, $this->sale->voided_by);

        // Verify Product stock was reversed: 8 + 2 = 10
        $this->product->refresh();
        $this->assertEquals(10.000, (float)$this->product->stock_quantity);

        // Verify StockMovement recorded
        $movement = StockMovement::where('product_id', $this->product->id)
            ->where('type', StockMovement::TYPE_SALE_RETURN)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(2.000, (float)$movement->quantity);
        $this->assertEquals(8.000, (float)$movement->previous_quantity);
        $this->assertEquals(10.000, (float)$movement->new_quantity);
    }

    public function test_cashier_cannot_void_sale(): void
    {
        $this->actingAs($this->cashier);

        $response = $this->post('/app/sales/' . $this->sale->id . '/void', [
            'reason' => 'Unauthorized void attempt',
        ]);

        $response->assertStatus(403);

        $this->sale->refresh();
        $this->assertEquals(Sale::STATUS_COMPLETED, $this->sale->status);
    }
}
