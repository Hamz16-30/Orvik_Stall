<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReportExportSecurityTest extends TestCase
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
            'name' => 'Main Stall',
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

    public function test_csv_export_stream_downloads_successfully(): void
    {
        $this->actingAs($this->owner);

        Sale::create([
            'business_id' => $this->business->id,
            'invoice_number' => 'INV-EXP-01',
            'subtotal' => 1500.00,
            'total_amount' => 1500.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $response = $this->get('/app/reports/sales/export/csv?period=this_month');
        $response->assertOk();
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment; filename=', $response->headers->get('content-disposition'));
    }

    public function test_excel_export_stream_downloads_successfully(): void
    {
        $this->actingAs($this->owner);

        Sale::create([
            'business_id' => $this->business->id,
            'invoice_number' => 'INV-EXP-02',
            'subtotal' => 2500.00,
            'total_amount' => 2500.00,
            'payment_method' => 'card',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $response = $this->get('/app/reports/sales/export/excel?period=this_month');
        $response->assertOk();
        $this->assertEquals('application/vnd.ms-excel; charset=UTF-8', $response->headers->get('content-type'));
    }

    public function test_cashier_strictly_forbidden_from_financial_reports_and_exports(): void
    {
        $this->actingAs($this->cashier);

        // 1. Profit report view
        $profitViewResponse = $this->get('/app/reports/profit');
        $profitViewResponse->assertForbidden();

        // 2. Profit CSV export
        $profitCsvResponse = $this->get('/app/reports/profit/export/csv');
        $profitCsvResponse->assertForbidden();

        // 3. Expenses report view
        $expenseViewResponse = $this->get('/app/reports/expenses');
        $expenseViewResponse->assertForbidden();

        // 4. Expenses CSV export
        $expenseCsvResponse = $this->get('/app/reports/expenses/export/csv');
        $expenseCsvResponse->assertForbidden();

        // 5. Purchases report view
        $purchaseViewResponse = $this->get('/app/reports/purchases');
        $purchaseViewResponse->assertForbidden();
    }
}
