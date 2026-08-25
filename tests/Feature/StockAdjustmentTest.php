<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected User $owner;
    protected User $cashier;
    protected Product $product;

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
            'name' => 'Demo Stall 01',
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
            'name' => 'Store Cashier',
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
            'selling_price' => 50.00,
            'cost_price' => 30.00,
            'stock_quantity' => 100.000,
            'low_stock_threshold' => 15.000,
            'unit' => 'bottle',
        ]);
    }

    public function test_stock_adjustments_math_and_movements_tracking(): void
    {
        $this->actingAs($this->owner);

        // 1. ADD 20 -> Stock should become 120
        $response1 = $this->post('/app/inventory/' . $this->product->id . '/stock-adjustment', [
            'type' => 'ADD',
            'quantity' => 20.000,
            'reason' => 'Received stock shipment',
        ]);
        $response1->assertRedirect('/app/inventory/' . $this->product->id);

        $this->product->refresh();
        $this->assertEquals(120.000, (float)$this->product->stock_quantity);

        $movement1 = StockMovement::where('product_id', $this->product->id)->latest('created_at')->first();
        $this->assertEquals(StockMovement::TYPE_ADJUSTMENT_ADD, $movement1->type);
        $this->assertEquals(100.000, (float)$movement1->previous_quantity);
        $this->assertEquals(120.000, (float)$movement1->new_quantity);
        $this->assertEquals('Received stock shipment', $movement1->reason);

        // 2. REMOVE 30 -> Stock should become 90
        $response2 = $this->post('/app/inventory/' . $this->product->id . '/stock-adjustment', [
            'type' => 'REMOVE',
            'quantity' => 30.000,
            'reason' => 'Expired batches written off',
        ]);
        $response2->assertRedirect('/app/inventory/' . $this->product->id);

        $this->product->refresh();
        $this->assertEquals(90.000, (float)$this->product->stock_quantity);

        // 3. SET 50 -> Stock should become 50
        $response3 = $this->post('/app/inventory/' . $this->product->id . '/stock-adjustment', [
            'type' => 'SET',
            'quantity' => 50.000,
            'reason' => 'Physical stock count correction',
        ]);
        $response3->assertRedirect('/app/inventory/' . $this->product->id);

        $this->product->refresh();
        $this->assertEquals(50.000, (float)$this->product->stock_quantity);

        // 4. Attempt REMOVE 100 (when stock is only 50) -> Fails with validation error
        $response4 = $this->from('/app/inventory/' . $this->product->id)->post('/app/inventory/' . $this->product->id . '/stock-adjustment', [
            'type' => 'REMOVE',
            'quantity' => 100.000,
            'reason' => 'Excessive removal',
        ]);
        $response4->assertSessionHasErrors('quantity');

        $this->product->refresh();
        $this->assertEquals(50.000, (float)$this->product->stock_quantity); // Stock unchanged!
    }

    public function test_cashier_cannot_make_stock_adjustments(): void
    {
        $this->actingAs($this->cashier);

        $response = $this->post('/app/inventory/' . $this->product->id . '/stock-adjustment', [
            'type' => 'ADD',
            'quantity' => 10.000,
        ]);

        $response->assertStatus(403);
    }
}
