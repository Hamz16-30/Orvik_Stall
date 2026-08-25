<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\Reports\InventoryReportService;
use App\Services\Reports\ProductReportService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InventoryReportTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected User $owner;
    protected InventoryReportService $inventoryReportService;
    protected ProductReportService $productReportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->inventoryReportService = app(InventoryReportService::class);
        $this->productReportService = app(ProductReportService::class);

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
        ]);

        BusinessUser::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'role_id' => $ownerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    public function test_inventory_valuation_and_status_report(): void
    {
        $this->actingAs($this->owner);

        $cat = Category::create(['business_id' => $this->business->id, 'name' => 'Beverages']);

        // In Stock (100 @ 50 = 5,000)
        Product::create([
            'business_id' => $this->business->id,
            'category_id' => $cat->id,
            'name' => 'Juice Box',
            'cost_price' => 50.00,
            'selling_price' => 80.00,
            'stock_quantity' => 100,
            'low_stock_threshold' => 10,
            'track_inventory' => true,
        ]);

        // Low Stock (5 @ 100 = 500)
        Product::create([
            'business_id' => $this->business->id,
            'category_id' => $cat->id,
            'name' => 'Soda Can',
            'cost_price' => 100.00,
            'selling_price' => 150.00,
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
            'track_inventory' => true,
        ]);

        // Out of Stock (0 @ 200 = 0)
        Product::create([
            'business_id' => $this->business->id,
            'category_id' => $cat->id,
            'name' => 'Energy Drink',
            'cost_price' => 200.00,
            'selling_price' => 300.00,
            'stock_quantity' => 0,
            'low_stock_threshold' => 10,
            'track_inventory' => true,
        ]);

        $report = $this->inventoryReportService->getInventoryStatusReport($this->business);

        $this->assertEquals(3, $report['summary']['total_products']);
        $this->assertEquals(5500.00, $report['summary']['total_valuation']); // 5,000 + 500 + 0
        $this->assertEquals(1, $report['summary']['out_of_stock']);
        $this->assertEquals(1, $report['summary']['low_stock']);

        $response = $this->get('/app/reports/inventory');
        $response->assertOk();
        $response->assertSee('5,500.00');
        $response->assertSee('Juice Box');
        $response->assertSee('Soda Can');
    }

    public function test_stock_movements_audit_report(): void
    {
        $this->actingAs($this->owner);

        $prod = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Mineral Water',
            'cost_price' => 20.00,
            'selling_price' => 40.00,
            'stock_quantity' => 50,
            'track_inventory' => true,
        ]);

        StockMovement::create([
            'business_id' => $this->business->id,
            'product_id' => $prod->id,
            'user_id' => $this->owner->id,
            'type' => 'PURCHASE',
            'quantity' => 50,
            'previous_quantity' => 0,
            'new_quantity' => 50,
            'reason' => 'PO-001',
        ]);

        $response = $this->get('/app/reports/inventory/movements');
        $response->assertOk();
        $response->assertSee('Mineral Water');
        $response->assertSee('PO-001');
    }

    public function test_product_performance_report_metrics(): void
    {
        $this->actingAs($this->owner);

        $prod = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Gourmet Burger',
            'cost_price' => 300.00,
            'selling_price' => 500.00,
            'stock_quantity' => 50,
        ]);

        $sale = Sale::create([
            'business_id' => $this->business->id,
            'invoice_number' => 'INV-PRD-1',
            'subtotal' => 2500.00,
            'total_amount' => 2500.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $prod->id,
            'product_name' => $prod->name,
            'unit_price' => 500.00,
            'cost_price' => 300.00,
            'quantity' => 5,
            'line_total' => 2500.00,
        ]);

        $report = $this->productReportService->getReport($this->business);

        $this->assertEquals(5, $report['summary']['total_units']);
        $this->assertEquals(2500.00, $report['summary']['total_revenue']);
        $this->assertEquals(1500.00, $report['summary']['total_cogs']); // 5 * 300
        $this->assertEquals(1000.00, $report['summary']['total_profit']); // 2500 - 1500
        $this->assertEquals(40.0, $report['summary']['average_margin']); // (1000 / 2500) * 100

        $response = $this->get('/app/reports/products');
        $response->assertOk();
        $response->assertSee('Gourmet Burger');
        $response->assertSee('2,500.00');
        $response->assertSee('1,000.00');
    }
}
