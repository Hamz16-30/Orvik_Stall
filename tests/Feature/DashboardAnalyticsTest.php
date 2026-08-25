<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\AnalyticsService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected User $owner;
    protected User $cashier;
    protected AnalyticsService $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->analyticsService = app(AnalyticsService::class);

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

    public function test_kpi_calculations_and_period_comparison(): void
    {
        $this->actingAs($this->owner);

        $prod = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Ice Cream Tub',
            'cost_price' => 60.00,
            'selling_price' => 100.00,
            'stock_quantity' => 500,
        ]);

        // 1. Yesterday Sale: 80 units @ 100 = 8,000, cost = 60 * 80 = 4,800
        $yesterday = now('Asia/Karachi')->subDay();
        $saleYesterday = Sale::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'invoice_number' => 'INV-000001',
            'subtotal' => 8000.00,
            'total_amount' => 8000.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => $yesterday,
        ]);
        SaleItem::create([
            'sale_id' => $saleYesterday->id,
            'product_id' => $prod->id,
            'product_name' => $prod->name,
            'unit_price' => 100.00,
            'cost_price' => 60.00,
            'quantity' => 80,
            'line_total' => 8000.00,
        ]);

        // 2. Today Sale: 100 units @ 100 = 10,000, cost = 60 * 100 = 6,000 -> Gross Profit: 4,000
        $today = now('Asia/Karachi');
        $saleToday = Sale::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'invoice_number' => 'INV-000002',
            'subtotal' => 10000.00,
            'total_amount' => 10000.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => $today,
        ]);
        SaleItem::create([
            'sale_id' => $saleToday->id,
            'product_id' => $prod->id,
            'product_name' => $prod->name,
            'unit_price' => 100.00,
            'cost_price' => 60.00,
            'quantity' => 100,
            'line_total' => 10000.00,
        ]);

        // 3. Today Expense: 1,000 -> Estimated Operating Profit: 4,000 - 1,000 = 3,000
        Expense::create([
            'business_id' => $this->business->id,
            'description' => 'Packaging Materials',
            'amount' => 1000.00,
            'payment_method' => 'cash',
            'expense_date' => $today->toDateString(),
            'status' => Expense::STATUS_ACTIVE,
        ]);

        $dateRange = $this->analyticsService->resolveDateRange('today', null, null, 'Asia/Karachi');
        $kpis = $this->analyticsService->getKPIs($this->business, $dateRange, true);

        // Verification
        $this->assertEquals(10000.00, $kpis['revenue']);
        $this->assertEquals(8000.00, $kpis['prev_revenue']);
        $this->assertEquals(25.0, $kpis['revenue_change']['value']); // ((10000 - 8000) / 8000) * 100 = +25%
        $this->assertEquals(1, $kpis['orders_count']);
        $this->assertEquals(4000.00, $kpis['gross_profit']); // 10000 - 6000
        $this->assertEquals(40.0, $kpis['gross_margin']); // (4000 / 10000) * 100
        $this->assertEquals(1000.00, $kpis['expenses']);
        $this->assertEquals(3000.00, $kpis['operating_profit']); // 4000 - 1000

        // Dashboard Route Verification
        $response = $this->get('/app/dashboard');
        $response->assertOk();
        $response->assertSee('10,000.00');
        $response->assertSee('4,000.00');
        $response->assertSee('1,000.00');
    }

    public function test_inventory_health_classification(): void
    {
        $this->actingAs($this->owner);

        // 1. Out of Stock
        $outOfStock = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Out Stock Item',
            'selling_price' => 50.00,
            'stock_quantity' => 0,
            'track_inventory' => true,
        ]);

        // 2. Low Stock (with a sale 5 days ago)
        $lowStock = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Low Stock Item',
            'selling_price' => 50.00,
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
            'track_inventory' => true,
        ]);
        $recentSale = Sale::create([
            'business_id' => $this->business->id,
            'invoice_number' => 'INV-REC-01',
            'subtotal' => 50.00,
            'total_amount' => 50.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now()->subDays(5),
        ]);
        SaleItem::create([
            'sale_id' => $recentSale->id,
            'product_id' => $lowStock->id,
            'product_name' => $lowStock->name,
            'unit_price' => 50.00,
            'quantity' => 1,
            'line_total' => 50.00,
        ]);

        // 3. Dead Stock (Stock > 0 and last sale 45 days ago)
        $deadStock = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Dead Stock Item',
            'selling_price' => 50.00,
            'stock_quantity' => 20,
            'track_inventory' => true,
        ]);
        $oldSale = Sale::create([
            'business_id' => $this->business->id,
            'invoice_number' => 'INV-OLD-01',
            'subtotal' => 50.00,
            'total_amount' => 50.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now()->subDays(45),
        ]);
        DB::table('sales')->where('id', $oldSale->id)->update(['completed_at' => now()->subDays(45), 'created_at' => now()->subDays(45)]);

        $oldItem = SaleItem::create([
            'sale_id' => $oldSale->id,
            'product_id' => $deadStock->id,
            'product_name' => $deadStock->name,
            'unit_price' => 50.00,
            'quantity' => 1,
            'line_total' => 50.00,
        ]);
        DB::table('sale_items')->where('id', $oldItem->id)->update(['created_at' => now()->subDays(45)]);

        // 4. Never Sold
        $neverSold = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Never Sold Item',
            'selling_price' => 50.00,
            'stock_quantity' => 30,
            'track_inventory' => true,
        ]);

        $health = $this->analyticsService->getInventoryHealth($this->business, 30);

        $this->assertEquals(1, $health['out_of_stock_count']);
        $this->assertEquals('Out Stock Item', $health['out_of_stock']->first()->name);

        $this->assertEquals(1, $health['low_stock_count']);
        $this->assertEquals('Low Stock Item', $health['low_stock']->first()->name);

        $this->assertEquals(1, $health['dead_stock_count']);
        $this->assertEquals('Dead Stock Item', $health['dead_stock']->first()['product']->name);

        $this->assertEquals(1, $health['never_sold_count']);
        $this->assertEquals('Never Sold Item', $health['never_sold']->first()['product']->name);
    }

    public function test_role_security_cashier_restricted_from_financials(): void
    {
        $this->actingAs($this->cashier);

        $response = $this->get('/app/dashboard');
        $response->assertOk();

        // Cashier sees Sales and Orders but cannot see Gross Profit or Expenses
        $response->assertViewHas('canViewFinancials', false);
    }

    public function test_tenant_isolation_on_dashboard_analytics(): void
    {
        $ownerRole = Role::where('name', Role::OWNER)->first();

        // Business B setup
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

        // Create 10,000 for Business A
        Sale::create([
            'business_id' => $this->business->id,
            'invoice_number' => 'INV-A-1',
            'subtotal' => 10000.00,
            'total_amount' => 10000.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        // Create 50,000 for Business B
        Sale::create([
            'business_id' => $businessB->id,
            'invoice_number' => 'INV-B-1',
            'subtotal' => 50000.00,
            'total_amount' => 50000.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $this->actingAs($this->owner);
        $response = $this->get('/app/dashboard');
        $response->assertOk();
        $response->assertSee('10,000.00');
        $response->assertDontSee('50,000.00');
        $response->assertDontSee('60,000.00');
    }

    public function test_date_range_filtering_today_vs_yesterday(): void
    {
        $this->actingAs($this->owner);

        // Yesterday sale
        Sale::create([
            'business_id' => $this->business->id,
            'invoice_number' => 'INV-YEST',
            'subtotal' => 5000.00,
            'total_amount' => 5000.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now('Asia/Karachi')->subDay(),
        ]);

        // Today sale
        Sale::create([
            'business_id' => $this->business->id,
            'invoice_number' => 'INV-TODAY',
            'subtotal' => 12000.00,
            'total_amount' => 12000.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now('Asia/Karachi'),
        ]);

        // Today view
        $todayResponse = $this->get('/app/dashboard?period=today');
        $todayResponse->assertOk();
        $todayResponse->assertSee('12,000.00');

        // Yesterday view
        $yesterdayResponse = $this->get('/app/dashboard?period=yesterday');
        $yesterdayResponse->assertOk();
        $yesterdayResponse->assertSee('5,000.00');
    }
}
