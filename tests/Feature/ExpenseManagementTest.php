<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExpenseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected User $owner;
    protected ExpenseCategory $category;

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

        $this->category = ExpenseCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Utilities',
            'description' => 'Electricity and water bills',
        ]);
    }

    public function test_expense_creation_and_voiding(): void
    {
        $this->actingAs($this->owner);

        // 1. Create Expense
        $response = $this->post('/app/expenses', [
            'category_id' => $this->category->id,
            'description' => 'August Electricity Bill',
            'amount' => 4500.00,
            'payment_method' => 'cash',
            'reference' => 'BILL-88291',
            'expense_date' => now()->toDateString(),
        ]);

        $response->assertRedirect('/app/expenses');

        $expense = Expense::where('description', 'August Electricity Bill')->first();
        $this->assertNotNull($expense);
        $this->assertEquals(4500.00, (float)$expense->amount);
        $this->assertEquals(Expense::STATUS_ACTIVE, $expense->status);

        // 2. Void Expense
        $voidResponse = $this->post('/app/expenses/' . $expense->id . '/void', [
            'reason' => 'Billed twice by mistake',
        ]);
        $voidResponse->assertRedirect('/app/expenses');

        $expense->refresh();
        $this->assertEquals(Expense::STATUS_VOIDED, $expense->status);
        $this->assertEquals('Billed twice by mistake', $expense->void_reason);
    }
}
