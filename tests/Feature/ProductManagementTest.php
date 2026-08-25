<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected User $owner;
    protected User $cashier;

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
    }

    public function test_owner_can_create_product_with_initial_opening_stock(): void
    {
        $this->actingAs($this->owner);

        $response = $this->post('/app/inventory', [
            'name' => 'Fresh Orange Juice 500ml',
            'selling_price' => 150.00,
            'cost_price' => 90.00,
            'opening_stock' => 20.000,
            'low_stock_threshold' => 5.000,
            'unit' => 'bottle',
            'sku' => 'OJ-500',
        ]);

        $product = Product::where('name', 'Fresh Orange Juice 500ml')->first();
        $this->assertNotNull($product);
        $this->assertEquals(20.000, (float)$product->stock_quantity);
        $this->assertEquals('IN_STOCK', $product->stock_status);

        $response->assertRedirect('/app/inventory/' . $product->id);

        // Verify initial opening stock movement was created
        $movement = StockMovement::where('product_id', $product->id)->first();
        $this->assertNotNull($movement);
        $this->assertEquals(StockMovement::TYPE_OPENING, $movement->type);
        $this->assertEquals(20.000, (float)$movement->quantity);
        $this->assertEquals(20.000, (float)$movement->new_quantity);
    }

    public function test_product_update_does_not_modify_stock_quantity(): void
    {
        $this->actingAs($this->owner);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Potato Chips',
            'selling_price' => 50.00,
            'cost_price' => 30.00,
            'stock_quantity' => 45.000,
            'low_stock_threshold' => 10.000,
            'unit' => 'pack',
        ]);

        $response = $this->put('/app/inventory/' . $product->id, [
            'name' => 'Gourmet Potato Chips Salted',
            'selling_price' => 60.00,
            'cost_price' => 35.00,
            'low_stock_threshold' => 8.000,
            'unit' => 'pack',
            'opening_stock' => 999.000, // Should be ignored by update endpoint
        ]);

        $response->assertRedirect('/app/inventory/' . $product->id);

        $product->refresh();
        $this->assertEquals('Gourmet Potato Chips Salted', $product->name);
        $this->assertEquals(60.00, (float)$product->selling_price);
        $this->assertEquals(45.000, (float)$product->stock_quantity); // Stock remains unchanged!
    }

    public function test_product_can_be_archived_and_restored(): void
    {
        $this->actingAs($this->owner);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Seasonal Mango Juice',
            'selling_price' => 120.00,
            'stock_quantity' => 15.000,
        ]);

        // 1. Archive
        $deleteResponse = $this->delete('/app/inventory/' . $product->id);
        $deleteResponse->assertRedirect('/app/inventory');

        $this->assertSoftDeleted('products', ['id' => $product->id]);

        // 2. Restore
        $restoreResponse = $this->post('/app/inventory/' . $product->id . '/restore');
        $restoreResponse->assertRedirect('/app/inventory/' . $product->id);

        $product->refresh();
        $this->assertFalse($product->trashed());
        $this->assertEquals(15.000, (float)$product->stock_quantity);
    }

    public function test_sku_uniqueness_is_scoped_per_business(): void
    {
        $this->actingAs($this->owner);

        Product::create([
            'business_id' => $this->business->id,
            'name' => 'Product 1',
            'sku' => 'SKU-UNIQUE-100',
            'selling_price' => 10.00,
        ]);

        // Creating product with duplicate SKU in same business fails
        $response = $this->from('/app/inventory/create')->post('/app/inventory', [
            'name' => 'Product 2',
            'sku' => 'SKU-UNIQUE-100',
            'selling_price' => 15.00,
        ]);

        $response->assertSessionHasErrors('sku');
    }

    public function test_cashier_cannot_view_cost_price(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Secret Cost Mineral Water',
            'cost_price' => 38.50,
            'selling_price' => 60.00,
            'stock_quantity' => 10.000,
        ]);

        $this->actingAs($this->cashier);

        $response = $this->get('/app/inventory/' . $product->id);
        $response->assertOk();
        $response->assertSee('Secret Cost Mineral Water');
        $response->assertDontSee('38.50'); // Sensitive cost price is not leaked!
    }
}
