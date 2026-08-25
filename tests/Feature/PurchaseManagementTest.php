<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PurchaseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected User $owner;
    protected Supplier $supplier;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $ownerRole = Role::where('name', Role::OWNER)->first();

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

        $this->supplier = Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'Al-Madina Distributors',
            'phone' => '03001112233',
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Mineral Water 500ml',
            'cost_price' => 40.00,
            'selling_price' => 60.00,
            'stock_quantity' => 10.000,
            'track_inventory' => true,
        ]);
    }

    public function test_purchase_creation_increases_stock_and_updates_latest_cost(): void
    {
        $this->actingAs($this->owner);

        $response = $this->post('/app/purchases', [
            'supplier_id' => $this->supplier->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 20,
                    'unit_cost' => 45.00,
                ],
            ],
            'payment_method' => 'bank_transfer',
            'payment_status' => 'paid',
        ]);

        $purchase = Purchase::where('business_id', $this->business->id)->first();
        $this->assertNotNull($purchase);
        $this->assertEquals(900.00, (float)$purchase->total_amount);
        $this->assertEquals('completed', $purchase->status);
        $this->assertEquals('PUR-000001', $purchase->purchase_number);

        // Product stock increased from 10 to 30
        $this->product->refresh();
        $this->assertEquals(30.000, (float)$this->product->stock_quantity);
        // Cost price updated to latest purchase unit cost (45.00)
        $this->assertEquals(45.00, (float)$this->product->cost_price);

        // Stock movement recorded
        $movement = StockMovement::where('product_id', $this->product->id)
            ->where('type', StockMovement::TYPE_PURCHASE)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(20.000, (float)$movement->quantity);
        $this->assertEquals(10.000, (float)$movement->previous_quantity);
        $this->assertEquals(30.000, (float)$movement->new_quantity);
    }

    public function test_void_purchase_reverses_inventory(): void
    {
        $this->actingAs($this->owner);

        // 1. Create Purchase of 25 units
        $this->post('/app/purchases', [
            'supplier_id' => $this->supplier->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 25, 'unit_cost' => 42.00],
            ],
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $purchase = Purchase::latest('id')->first();
        $this->product->refresh();
        $this->assertEquals(35.000, (float)$this->product->stock_quantity); // 10 + 25 = 35

        // 2. Void Purchase
        $voidResponse = $this->post('/app/purchases/' . $purchase->id . '/void', [
            'reason' => 'Vendor delivered defective shipment',
        ]);
        $voidResponse->assertRedirect('/app/purchases/' . $purchase->id);

        $purchase->refresh();
        $this->assertEquals(Purchase::STATUS_VOIDED, $purchase->status);
        $this->assertEquals('Vendor delivered defective shipment', $purchase->void_reason);

        // Product stock reversed back from 35 to 10
        $this->product->refresh();
        $this->assertEquals(10.000, (float)$this->product->stock_quantity);

        // Reversal movement recorded
        $revMovement = StockMovement::where('product_id', $this->product->id)
            ->where('type', StockMovement::TYPE_PURCHASE_VOID)
            ->first();
        $this->assertNotNull($revMovement);
        $this->assertEquals(-25.000, (float)$revMovement->quantity);
        $this->assertEquals(35.000, (float)$revMovement->previous_quantity);
        $this->assertEquals(10.000, (float)$revMovement->new_quantity);
    }
}
