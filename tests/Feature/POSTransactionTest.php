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

class POSTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected User $owner;
    protected User $cashier;
    protected Product $productA;
    protected Product $productB;

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

        $this->productA = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Mineral Water 500ml',
            'sku' => 'WTR-500',
            'cost_price' => 40.00,
            'selling_price' => 60.00,
            'stock_quantity' => 10.000,
            'track_inventory' => true,
        ]);

        $this->productB = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Potato Chips 65g',
            'sku' => 'CHP-65',
            'cost_price' => 35.00,
            'selling_price' => 50.00,
            'stock_quantity' => 15.000,
            'track_inventory' => true,
        ]);
    }

    public function test_single_item_sale_creates_sale_and_deducts_stock(): void
    {
        $this->actingAs($this->cashier);

        $response = $this->postJson('/app/sales/complete', [
            'items' => [
                ['product_id' => $this->productA->id, 'quantity' => 2],
            ],
            'payment_method' => 'cash',
            'amount_received' => 200.00,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        // Verify Sale in database
        $sale = Sale::where('business_id', $this->business->id)->first();
        $this->assertNotNull($sale);
        $this->assertEquals(120.00, (float)$sale->subtotal); // 2 * 60
        $this->assertEquals(120.00, (float)$sale->total_amount);
        $this->assertEquals(200.00, (float)$sale->amount_received);
        $this->assertEquals(80.00, (float)$sale->change_amount);
        $this->assertEquals('completed', $sale->status);
        $this->assertEquals('INV-000001', $sale->invoice_number);

        // Verify SaleItem snapshot
        $saleItem = SaleItem::where('sale_id', $sale->id)->first();
        $this->assertNotNull($saleItem);
        $this->assertEquals('Mineral Water 500ml', $saleItem->product_name);
        $this->assertEquals(2.000, (float)$saleItem->quantity);
        $this->assertEquals(60.00, (float)$saleItem->unit_price);
        $this->assertEquals(40.00, (float)$saleItem->cost_price);
        $this->assertEquals(120.00, (float)$saleItem->line_total);

        // Verify Product Stock deducted: 10 - 2 = 8
        $this->productA->refresh();
        $this->assertEquals(8.000, (float)$this->productA->stock_quantity);

        // Verify StockMovement recorded
        $movement = StockMovement::where('product_id', $this->productA->id)
            ->where('type', StockMovement::TYPE_SALE)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(-2.000, (float)$movement->quantity);
        $this->assertEquals(10.000, (float)$movement->previous_quantity);
        $this->assertEquals(8.000, (float)$movement->new_quantity);
        $this->assertEquals($sale->id, $movement->reference_id);
    }

    public function test_multi_item_sale_with_discount_and_cash_change(): void
    {
        $this->actingAs($this->cashier);

        // 2 Water (2 * 60 = 120) + 1 Chips (1 * 50 = 50) = Subtotal 170
        // Discount 20 -> Total 150
        // Received 200 -> Change 50
        $response = $this->postJson('/app/sales/complete', [
            'items' => [
                ['product_id' => $this->productA->id, 'quantity' => 2],
                ['product_id' => $this->productB->id, 'quantity' => 1],
            ],
            'discount_amount' => 20.00,
            'payment_method' => 'cash',
            'amount_received' => 200.00,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $sale = Sale::latest('id')->first();
        $this->assertEquals(170.00, (float)$sale->subtotal);
        $this->assertEquals(20.00, (float)$sale->discount_amount);
        $this->assertEquals(150.00, (float)$sale->total_amount);
        $this->assertEquals(50.00, (float)$sale->change_amount);

        $this->productA->refresh();
        $this->productB->refresh();
        $this->assertEquals(8.000, (float)$this->productA->stock_quantity);
        $this->assertEquals(14.000, (float)$this->productB->stock_quantity);
    }

    public function test_sale_rejected_when_stock_is_insufficient(): void
    {
        $this->actingAs($this->cashier);

        // Product A only has 10 in stock; attempting to sell 15
        $response = $this->postJson('/app/sales/complete', [
            'items' => [
                ['product_id' => $this->productA->id, 'quantity' => 15],
            ],
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422);

        // Verify no sale and no stock deduction took place
        $this->assertEquals(0, Sale::count());
        $this->productA->refresh();
        $this->assertEquals(10.000, (float)$this->productA->stock_quantity);
    }

    public function test_price_tampering_is_rejected_and_server_price_is_used(): void
    {
        $this->actingAs($this->cashier);

        // Even if rogue client submits price = 1.00, server must calculate using DB price 60.00
        $response = $this->postJson('/app/sales/complete', [
            'items' => [
                ['product_id' => $this->productA->id, 'quantity' => 2, 'unit_price' => 1.00],
            ],
            'payment_method' => 'cash',
        ]);

        $response->assertOk();

        $sale = Sale::first();
        $this->assertEquals(120.00, (float)$sale->total_amount);
    }

    public function test_duplicate_submission_protection_via_idempotency_token(): void
    {
        $this->actingAs($this->cashier);

        $token = 'unique_idempotency_token_12345';

        // 1st request
        $res1 = $this->postJson('/app/sales/complete', [
            'items' => [['product_id' => $this->productA->id, 'quantity' => 1]],
            'payment_method' => 'cash',
            'idempotency_token' => $token,
        ]);
        $res1->assertOk();

        // 2nd duplicate request with same token
        $res2 = $this->postJson('/app/sales/complete', [
            'items' => [['product_id' => $this->productA->id, 'quantity' => 1]],
            'payment_method' => 'cash',
            'idempotency_token' => $token,
        ]);
        $res2->assertOk();

        // Only ONE sale was created and stock only deducted once (10 - 1 = 9)
        $this->assertEquals(1, Sale::count());
        $this->productA->refresh();
        $this->assertEquals(9.000, (float)$this->productA->stock_quantity);
    }

    public function test_archived_product_cannot_be_sold(): void
    {
        $this->actingAs($this->cashier);

        $this->productA->delete(); // Soft delete / archive

        $response = $this->postJson('/app/sales/complete', [
            'items' => [
                ['product_id' => $this->productA->id, 'quantity' => 1],
            ],
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422);
        $this->assertEquals(0, Sale::count());
    }
}
