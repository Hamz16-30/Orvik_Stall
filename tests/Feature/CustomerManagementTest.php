<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
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

    public function test_customer_crud_and_archiving(): void
    {
        $this->actingAs($this->owner);

        // 1. Create
        $response = $this->post('/app/customers', [
            'name' => 'Ali Raza',
            'phone' => '03001234567',
            'email' => 'ali@example.com',
            'address' => 'Shop 12, Main Bazaar',
        ]);

        $customer = Customer::where('name', 'Ali Raza')->first();
        $this->assertNotNull($customer);
        $response->assertRedirect('/app/customers/' . $customer->id);

        // 2. Update
        $updateResponse = $this->put('/app/customers/' . $customer->id, [
            'name' => 'Ali Raza Khan',
            'phone' => '03009999999',
            'status' => 'active',
        ]);
        $updateResponse->assertRedirect('/app/customers/' . $customer->id);

        $customer->refresh();
        $this->assertEquals('Ali Raza Khan', $customer->name);
        $this->assertEquals('03009999999', $customer->phone);

        // 3. Archive (Soft-delete)
        $deleteResponse = $this->delete('/app/customers/' . $customer->id);
        $deleteResponse->assertRedirect('/app/customers');

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_quick_customer_creation_from_pos_and_sale_association(): void
    {
        $this->actingAs($this->cashier);

        // Quick create via JSON/AJAX
        $response = $this->postJson('/app/customers', [
            'name' => 'Walk-in VIP',
            'phone' => '03211112233',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $customerId = $response->json('customer.id');

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Juice Bottle',
            'selling_price' => 100.00,
            'stock_quantity' => 10.000,
            'track_inventory' => true,
        ]);

        // Complete sale attached to customer
        $saleResponse = $this->postJson('/app/sales/complete', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'customer_id' => $customerId,
            'payment_method' => 'cash',
            'amount_received' => 200.00,
        ]);

        $saleResponse->assertOk();

        $sale = Sale::latest('id')->first();
        $this->assertEquals($customerId, $sale->customer_id);

        // View Customer Profile and verify metrics
        $customer = Customer::find($customerId);
        $this->assertEquals(1, $customer->total_orders);
        $this->assertEquals(200.00, $customer->total_spent);
    }
}
