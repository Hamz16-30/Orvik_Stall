<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OperationsTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_tenant_isolation_and_cashier_role_restrictions(): void
    {
        $ownerRole = Role::where('name', Role::OWNER)->first();
        $cashierRole = Role::where('name', Role::CASHIER)->first();

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

        $cashierA = User::create([
            'name' => 'Cashier A',
            'email' => 'cashier_a@orvikstore.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
        BusinessUser::create([
            'business_id' => $businessA->id,
            'user_id' => $cashierA->id,
            'role_id' => $cashierRole->id,
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

        $customerB = Customer::create([
            'business_id' => $businessB->id,
            'name' => 'Secret Customer B',
        ]);
        $supplierB = Supplier::create([
            'business_id' => $businessB->id,
            'name' => 'Secret Supplier B',
        ]);
        $purchaseB = Purchase::create([
            'business_id' => $businessB->id,
            'purchase_number' => 'PUR-000001',
            'subtotal' => 1000.00,
            'total_amount' => 1000.00,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);
        $expenseB = Expense::create([
            'business_id' => $businessB->id,
            'description' => 'Secret Expense B',
            'amount' => 500.00,
            'expense_date' => now()->toDateString(),
        ]);

        // 3. User A (Owner of Stall A) cannot view Business B entities
        $this->actingAs($userA);

        $this->assertTrue(in_array($this->get('/app/customers/' . $customerB->id)->status(), [403, 404]));
        $this->assertTrue(in_array($this->get('/app/suppliers/' . $supplierB->id)->status(), [403, 404]));
        $this->assertTrue(in_array($this->get('/app/purchases/' . $purchaseB->id)->status(), [403, 404]));

        // 4. Cashier A is forbidden from Purchases and Expenses modules
        $this->actingAs($cashierA);

        $this->get('/app/purchases')->assertStatus(403);
        $this->get('/app/purchases/create')->assertStatus(403);
        $this->get('/app/expenses')->assertStatus(403);
        $this->get('/app/expenses/create')->assertStatus(403);
    }
}
