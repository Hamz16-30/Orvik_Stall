<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\Reports\ProfitReportService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfitReportTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected User $owner;
    protected ProfitReportService $profitReportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->profitReportService = app(ProfitReportService::class);

        $ownerRole = Role::where('name', Role::OWNER)->first();

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
    }

    public function test_profit_and_loss_waterfall_reconciliation(): void
    {
        $this->actingAs($this->owner);

        // Product
        $prod = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Biryani Box',
            'cost_price' => 150.00,
            'selling_price' => 250.00,
            'stock_quantity' => 100,
        ]);

        // Sale: 40 units @ 250 = 10,000, COGS = 40 * 150 = 6,000 -> Gross Profit: 4,000
        $sale = Sale::create([
            'business_id' => $this->business->id,
            'invoice_number' => 'INV-PRF-1',
            'subtotal' => 10000.00,
            'total_amount' => 10000.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now('Asia/Karachi'),
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $prod->id,
            'product_name' => $prod->name,
            'unit_price' => 250.00,
            'cost_price' => 150.00,
            'quantity' => 40,
            'line_total' => 10000.00,
        ]);

        // Operating Expenses: 1,000 -> Estimated Operating Profit: 4,000 - 1,000 = 3,000
        Expense::create([
            'business_id' => $this->business->id,
            'description' => 'Packaging and Fuel',
            'amount' => 1000.00,
            'payment_method' => 'cash',
            'expense_date' => now('Asia/Karachi')->toDateString(),
            'status' => Expense::STATUS_ACTIVE,
        ]);

        $report = $this->profitReportService->getReport($this->business, 'today');

        $this->assertEquals(10000.00, $report['kpis']['revenue']);
        $this->assertEquals(4000.00, $report['kpis']['gross_profit']); // 10000 - 6000
        $this->assertEquals(40.0, $report['kpis']['gross_margin']);
        $this->assertEquals(1000.00, $report['kpis']['expenses']);
        $this->assertEquals(3000.00, $report['kpis']['operating_profit']); // 4000 - 1000

        $response = $this->get('/app/reports/profit?period=today');
        $response->assertOk();
        $response->assertSee('10,000.00');
        $response->assertSee('4,000.00');
        $response->assertSee('3,000.00');
    }
}
