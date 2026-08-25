<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\Reports\SalesReportService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SalesReportTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected User $owner;
    protected User $cashier;
    protected Customer $customer;
    protected SalesReportService $salesReportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->salesReportService = app(SalesReportService::class);

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
            'timezone' => 'Asia/Karachi',
        ]);

        BusinessUser::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'role_id' => $ownerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->cashier = User::create([
            'name' => 'Staff Cashier',
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

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Hamza Ali',
            'phone' => '+923001234567',
        ]);
    }

    public function test_sales_report_calculations_and_reconciliation(): void
    {
        $this->actingAs($this->owner);

        // Sale 1: Completed (Subtotal 5,000, Disc 500, Tax 200, Total 4,700)
        $sale1 = Sale::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-001',
            'subtotal' => 5000.00,
            'discount_amount' => 500.00,
            'tax_amount' => 200.00,
            'total_amount' => 4700.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now('Asia/Karachi'),
        ]);

        // Sale 2: Completed (Subtotal 3,000, Total 3,000)
        $sale2 = Sale::create([
            'business_id' => $this->business->id,
            'user_id' => $this->cashier->id,
            'invoice_number' => 'INV-002',
            'subtotal' => 3000.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'total_amount' => 3000.00,
            'payment_method' => 'card',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now('Asia/Karachi'),
        ]);

        // Sale 3: VOIDED (Subtotal 10,000, Total 10,000) -> MUST be excluded from totals
        Sale::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'invoice_number' => 'INV-VOID',
            'subtotal' => 10000.00,
            'total_amount' => 10000.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_VOIDED,
            'completed_at' => now('Asia/Karachi'),
        ]);

        $report = $this->salesReportService->getReport($this->business, [], 'today');

        $this->assertEquals(8000.00, $report['summary']['gross_sales']);
        $this->assertEquals(500.00, $report['summary']['discounts']);
        $this->assertEquals(200.00, $report['summary']['taxes']);
        $this->assertEquals(7700.00, $report['summary']['net_sales']);
        $this->assertEquals(2, $report['summary']['orders_count']);
        $this->assertEquals(3850.00, $report['summary']['average_order_value']); // 7700 / 2

        $response = $this->get('/app/reports/sales?period=today');
        $response->assertOk();
        $response->assertSee('7,700.00');
        $response->assertSee('INV-001');
        $response->assertSee('INV-002');
        $response->assertDontSee('INV-VOID');
    }

    public function test_grouped_sales_reports(): void
    {
        $this->actingAs($this->owner);

        Sale::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'invoice_number' => 'INV-GRP-1',
            'subtotal' => 4000.00,
            'total_amount' => 4000.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now('Asia/Karachi'),
        ]);

        $responseDaily = $this->get('/app/reports/sales/daily?period=today');
        $responseDaily->assertOk();
        $responseDaily->assertSee('4,000.00');

        $responseWeekly = $this->get('/app/reports/sales/weekly?period=this_week');
        $responseWeekly->assertOk();
        $responseWeekly->assertSee('4,000.00');

        $responseMonthly = $this->get('/app/reports/sales/monthly?period=this_month');
        $responseMonthly->assertOk();
        $responseMonthly->assertSee('4,000.00');
    }
}
