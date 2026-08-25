<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\Reports\CustomerReportService;
use App\Services\Reports\ExpenseReportService;
use App\Services\Reports\ExportService;
use App\Services\Reports\InventoryReportService;
use App\Services\Reports\PaymentReportService;
use App\Services\Reports\ProductReportService;
use App\Services\Reports\ProfitReportService;
use App\Services\Reports\PurchaseReportService;
use App\Services\Reports\SalesReportService;
use App\Services\Reports\SupplierReportService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected SalesReportService $salesReportService,
        protected ProductReportService $productReportService,
        protected InventoryReportService $inventoryReportService,
        protected PurchaseReportService $purchaseReportService,
        protected ExpenseReportService $expenseReportService,
        protected CustomerReportService $customerReportService,
        protected SupplierReportService $supplierReportService,
        protected PaymentReportService $paymentReportService,
        protected ProfitReportService $profitReportService,
        protected ExportService $exportService
    ) {}

    /**
     * Authorize granular report permission.
     */
    protected function authorizePermission(string $permission): void
    {
        $user = Auth::user();
        if (!$user || !$user->hasPermissionTo($permission)) {
            abort(403, 'You do not have the required permission (' . $permission . ') to access this report.');
        }
    }

    /**
     * Report Center Hub.
     */
    public function index(): View
    {
        $this->authorizePermission('reports.view');
        $business = $this->tenantContext->getCurrentBusiness();
        $user = Auth::user();

        AuditLogger::log('report.viewed', 'ReportCenter', null, [], $business->id, $user->id);

        return view('app.reports.index', [
            'business' => $business,
            'user' => $user,
        ]);
    }

    /**
     * Sales Report.
     */
    public function sales(Request $request): View
    {
        $this->authorizePermission('reports.sales');
        $business = $this->tenantContext->getCurrentBusiness();

        $period = $request->get('period', 'this_month');
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');
        $filters = $request->only(['payment_method', 'user_id', 'customer_id', 'status', 'search']);

        $data = $this->salesReportService->getReport($business, $filters, $period, $customStart, $customEnd, 25);
        $cashiers = User::whereHas('businesses', fn($q) => $q->where('businesses.id', $business->id))->get();
        $customers = Customer::where('business_id', $business->id)->get();

        return view('app.reports.sales', array_merge($data, [
            'business' => $business,
            'user' => Auth::user(),
            'period' => $period,
            'cashiers' => $cashiers,
            'customers' => $customers,
        ]));
    }

    public function dailySales(Request $request): View
    {
        $this->authorizePermission('reports.sales');
        $business = $this->tenantContext->getCurrentBusiness();
        $period = $request->get('period', 'this_month');
        $data = $this->salesReportService->getGroupedReport($business, 'daily', $period, $request->get('start_date'), $request->get('end_date'));

        return view('app.reports.sales-grouped', array_merge($data, [
            'business' => $business,
            'user' => Auth::user(),
            'period' => $period,
            'title' => 'Daily Sales Summary',
        ]));
    }

    public function weeklySales(Request $request): View
    {
        $this->authorizePermission('reports.sales');
        $business = $this->tenantContext->getCurrentBusiness();
        $period = $request->get('period', 'this_month');
        $data = $this->salesReportService->getGroupedReport($business, 'weekly', $period, $request->get('start_date'), $request->get('end_date'));

        return view('app.reports.sales-grouped', array_merge($data, [
            'business' => $business,
            'user' => Auth::user(),
            'period' => $period,
            'title' => 'Weekly Sales Summary',
        ]));
    }

    public function monthlySales(Request $request): View
    {
        $this->authorizePermission('reports.sales');
        $business = $this->tenantContext->getCurrentBusiness();
        $period = $request->get('period', 'all');
        $data = $this->salesReportService->getGroupedReport($business, 'monthly', $period, $request->get('start_date'), $request->get('end_date'));

        return view('app.reports.sales-grouped', array_merge($data, [
            'business' => $business,
            'user' => Auth::user(),
            'period' => $period,
            'title' => 'Monthly Sales Summary',
        ]));
    }

    /**
     * Product Performance Report.
     */
    public function products(Request $request): View
    {
        $this->authorizePermission('reports.products');
        $business = $this->tenantContext->getCurrentBusiness();

        $period = $request->get('period', 'this_month');
        $filters = $request->only(['category_id', 'search']);
        $sortBy = $request->get('sort_by', 'revenue');
        $sortDirection = $request->get('sort_dir', 'desc');

        $data = $this->productReportService->getReport(
            $business,
            $filters,
            $period,
            $request->get('start_date'),
            $request->get('end_date'),
            $sortBy,
            $sortDirection,
            25
        );

        $categories = Category::where('business_id', $business->id)->get();

        return view('app.reports.products', array_merge($data, [
            'business' => $business,
            'user' => Auth::user(),
            'period' => $period,
            'categories' => $categories,
        ]));
    }

    public function productCategories(Request $request): View
    {
        $this->authorizePermission('reports.products');
        $business = $this->tenantContext->getCurrentBusiness();
        $period = $request->get('period', 'this_month');
        $data = $this->productReportService->getCategoryReport($business, $period, $request->get('start_date'), $request->get('end_date'));

        return view('app.reports.product-categories', array_merge($data, [
            'business' => $business,
            'user' => Auth::user(),
            'period' => $period,
        ]));
    }

    /**
     * Inventory Status Report.
     */
    public function inventory(Request $request): View
    {
        $this->authorizePermission('reports.inventory');
        $business = $this->tenantContext->getCurrentBusiness();
        $filters = $request->only(['category_id', 'stock_status', 'search']);
        $data = $this->inventoryReportService->getInventoryStatusReport($business, $filters, 30, 25);
        $categories = Category::where('business_id', $business->id)->get();

        return view('app.reports.inventory', array_merge($data, [
            'business' => $business,
            'user' => Auth::user(),
            'categories' => $categories,
        ]));
    }

    public function stockMovements(Request $request): View
    {
        $this->authorizePermission('reports.inventory');
        $business = $this->tenantContext->getCurrentBusiness();
        $period = $request->get('period', 'this_month');
        $filters = $request->only(['movement_type', 'product_id', 'search']);

        $data = $this->inventoryReportService->getStockMovementsReport($business, $filters, $period, $request->get('start_date'), $request->get('end_date'), 25);
        $products = Product::where('business_id', $business->id)->get();

        return view('app.reports.inventory-movements', array_merge($data, [
            'business' => $business,
            'user' => Auth::user(),
            'period' => $period,
            'products' => $products,
        ]));
    }

    /**
     * Purchases Report.
     */
    public function purchases(Request $request): View
    {
        $this->authorizePermission('reports.purchases');
        $business = $this->tenantContext->getCurrentBusiness();
        $period = $request->get('period', 'this_month');
        $filters = $request->only(['supplier_id', 'payment_status', 'status', 'search']);

        $data = $this->purchaseReportService->getReport($business, $filters, $period, $request->get('start_date'), $request->get('end_date'), 25);
        $suppliers = Supplier::where('business_id', $business->id)->get();

        return view('app.reports.purchases', array_merge($data, [
            'business' => $business,
            'user' => Auth::user(),
            'period' => $period,
            'suppliers' => $suppliers,
        ]));
    }

    /**
     * Suppliers Report.
     */
    public function suppliers(Request $request): View
    {
        $this->authorizePermission('reports.suppliers');
        $business = $this->tenantContext->getCurrentBusiness();
        $period = $request->get('period', 'all');
        $filters = $request->only(['search']);

        $data = $this->supplierReportService->getReport($business, $filters, $period, $request->get('start_date'), $request->get('end_date'), 25);

        return view('app.reports.suppliers', array_merge($data, [
            'business' => $business,
            'user' => Auth::user(),
            'period' => $period,
        ]));
    }

    /**
     * Customers Report.
     */
    public function customers(Request $request): View
    {
        $this->authorizePermission('reports.customers');
        $business = $this->tenantContext->getCurrentBusiness();
        $period = $request->get('period', 'all');
        $filters = $request->only(['search']);
        $sortBy = $request->get('sort_by', 'spent');
        $sortDirection = $request->get('sort_dir', 'desc');

        $data = $this->customerReportService->getReport($business, $filters, $period, $request->get('start_date'), $request->get('end_date'), $sortBy, $sortDirection, 25);

        return view('app.reports.customers', array_merge($data, [
            'business' => $business,
            'user' => Auth::user(),
            'period' => $period,
        ]));
    }

    /**
     * Expenses Report.
     */
    public function expenses(Request $request): View
    {
        $this->authorizePermission('reports.expenses');
        $business = $this->tenantContext->getCurrentBusiness();
        $period = $request->get('period', 'this_month');
        $filters = $request->only(['category_id', 'payment_method', 'status', 'search']);

        $data = $this->expenseReportService->getReport($business, $filters, $period, $request->get('start_date'), $request->get('end_date'), 25);

        return view('app.reports.expenses', array_merge($data, [
            'business' => $business,
            'user' => Auth::user(),
            'period' => $period,
        ]));
    }

    /**
     * Payments Report.
     */
    public function payments(Request $request): View
    {
        $this->authorizePermission('reports.payments');
        $business = $this->tenantContext->getCurrentBusiness();
        $period = $request->get('period', 'this_month');
        $filters = $request->only(['payment_method']);

        $data = $this->paymentReportService->getReport($business, $filters, $period, $request->get('start_date'), $request->get('end_date'), 25);

        return view('app.reports.payments', array_merge($data, [
            'business' => $business,
            'user' => Auth::user(),
            'period' => $period,
        ]));
    }

    /**
     * Profitability & Margins Report.
     */
    public function profit(Request $request): View
    {
        $this->authorizePermission('reports.profit');
        $business = $this->tenantContext->getCurrentBusiness();
        $period = $request->get('period', 'this_month');

        $data = $this->profitReportService->getReport($business, $period, $request->get('start_date'), $request->get('end_date'));

        return view('app.reports.profit', array_merge($data, [
            'business' => $business,
            'user' => Auth::user(),
            'period' => $period,
        ]));
    }

    /**
     * Universal CSV Export Handler.
     */
    public function exportCsv(Request $request, string $type): StreamedResponse
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $user = Auth::user();
        $period = $request->get('period', 'this_month');

        switch ($type) {
            case 'sales':
                $this->authorizePermission('reports.sales');
                $rows = $this->salesReportService->getExportData($business, $request->all(), $period, $request->get('start_date'), $request->get('end_date'));
                $headers = ['Invoice Number', 'Date', 'Customer', 'Cashier', 'Subtotal', 'Discount', 'Tax', 'Total Amount', 'Payment Method', 'Status'];
                $formattedRows = $rows->map(fn($s) => [
                    $s->invoice_number,
                    $s->completed_at ? $s->completed_at->format('Y-m-d H:i') : '',
                    $s->customer ? $s->customer->name : 'Walk-in',
                    $s->user ? $s->user->name : '',
                    $s->subtotal,
                    $s->discount_amount,
                    $s->tax_amount,
                    $s->total_amount,
                    $s->payment_method,
                    $s->status,
                ]);
                $filename = "orvik-sales-{$period}-" . now()->format('Ymd') . ".csv";
                return $this->exportService->downloadCsv($filename, $headers, $formattedRows, $business, $user, 'sales');

            case 'products':
                $this->authorizePermission('reports.products');
                $data = $this->productReportService->getReport($business, $request->all(), $period, $request->get('start_date'), $request->get('end_date'), 'revenue', 'desc', 1000);
                $headers = ['Product Name', 'SKU', 'Category', 'Units Sold', 'Revenue', 'COGS', 'Gross Profit', 'Margin %'];
                $formattedRows = $data['all_rows']->map(fn($p) => [
                    $p->product_name,
                    $p->sku ?: '',
                    $p->category_name,
                    $p->units_sold,
                    $p->revenue,
                    $p->cogs,
                    $p->gross_profit,
                    $p->margin . '%',
                ]);
                $filename = "orvik-product-performance-{$period}-" . now()->format('Ymd') . ".csv";
                return $this->exportService->downloadCsv($filename, $headers, $formattedRows, $business, $user, 'products');

            case 'inventory':
                $this->authorizePermission('reports.inventory');
                $data = $this->inventoryReportService->getInventoryStatusReport($business, $request->all(), 30, 1000);
                $headers = ['Product Name', 'SKU', 'Category', 'Current Stock', 'Threshold', 'Unit', 'Cost Price', 'Selling Price', 'Estimated Value', 'Status', 'Last Sold'];
                $formattedRows = $data['all_rows']->map(fn($p) => [
                    $p->name,
                    $p->sku ?: '',
                    $p->category_name,
                    $p->stock_quantity,
                    $p->low_stock_threshold,
                    $p->unit,
                    $p->cost_price,
                    $p->selling_price,
                    $p->stock_value,
                    $p->status,
                    $p->last_sold_date,
                ]);
                $filename = "orvik-inventory-status-" . now()->format('Ymd') . ".csv";
                return $this->exportService->downloadCsv($filename, $headers, $formattedRows, $business, $user, 'inventory');

            case 'purchases':
                $this->authorizePermission('reports.purchases');
                $rows = $this->purchaseReportService->getExportData($business, $request->all(), $period, $request->get('start_date'), $request->get('end_date'));
                $headers = ['Purchase Number', 'Date', 'Supplier', 'Subtotal', 'Discount', 'Total Amount', 'Payment Method', 'Payment Status', 'Status'];
                $formattedRows = $rows->map(fn($p) => [
                    $p->purchase_number,
                    $p->purchased_at ? $p->purchased_at->format('Y-m-d') : '',
                    $p->supplier ? $p->supplier->name : 'Unassigned',
                    $p->subtotal,
                    $p->discount_amount,
                    $p->total_amount,
                    $p->payment_method,
                    $p->payment_status,
                    $p->status,
                ]);
                $filename = "orvik-purchases-{$period}-" . now()->format('Ymd') . ".csv";
                return $this->exportService->downloadCsv($filename, $headers, $formattedRows, $business, $user, 'purchases');

            case 'expenses':
                $this->authorizePermission('reports.expenses');
                $rows = $this->expenseReportService->getExportData($business, $request->all(), $period, $request->get('start_date'), $request->get('end_date'));
                $headers = ['Date', 'Category', 'Description', 'Amount', 'Payment Method', 'Reference', 'Recorded By', 'Status'];
                $formattedRows = $rows->map(fn($e) => [
                    $e->expense_date ? $e->expense_date->format('Y-m-d') : '',
                    $e->category ? $e->category->name : 'Uncategorized',
                    $e->description,
                    $e->amount,
                    $e->payment_method,
                    $e->reference ?: '',
                    $e->user ? $e->user->name : '',
                    $e->status,
                ]);
                $filename = "orvik-expenses-{$period}-" . now()->format('Ymd') . ".csv";
                return $this->exportService->downloadCsv($filename, $headers, $formattedRows, $business, $user, 'expenses');

            case 'customers':
                $this->authorizePermission('reports.customers');
                $data = $this->customerReportService->getReport($business, $request->all(), $period, $request->get('start_date'), $request->get('end_date'), 'spent', 'desc', 1000);
                $headers = ['Customer Name', 'Phone', 'Email', 'Total Orders', 'Total Spent', 'Average Order Value', 'Last Purchase'];
                $formattedRows = $data['all_rows']->map(fn($c) => [
                    $c->name,
                    $c->phone ?: '',
                    $c->email ?: '',
                    $c->orders_count,
                    $c->total_spent,
                    $c->average_order_value,
                    $c->last_purchase_date,
                ]);
                $filename = "orvik-customers-" . now()->format('Ymd') . ".csv";
                return $this->exportService->downloadCsv($filename, $headers, $formattedRows, $business, $user, 'customers');

            case 'suppliers':
                $this->authorizePermission('reports.suppliers');
                $data = $this->supplierReportService->getReport($business, $request->all(), $period, $request->get('start_date'), $request->get('end_date'), 1000);
                $headers = ['Supplier Name', 'Company Name', 'Phone', 'Total Purchases', 'Total Value', 'Paid Amount', 'Unpaid Amount', 'Last Purchase'];
                $formattedRows = $data['all_rows']->map(fn($s) => [
                    $s->name,
                    $s->company_name ?: '',
                    $s->phone ?: '',
                    $s->purchases_count,
                    $s->total_purchases_value,
                    $s->paid_amount,
                    $s->unpaid_amount,
                    $s->last_purchase_date,
                ]);
                $filename = "orvik-suppliers-" . now()->format('Ymd') . ".csv";
                return $this->exportService->downloadCsv($filename, $headers, $formattedRows, $business, $user, 'suppliers');

            case 'profit':
                $this->authorizePermission('reports.profit');
                $data = $this->profitReportService->getReport($business, $period, $request->get('start_date'), $request->get('end_date'));
                $headers = ['Date', 'Revenue', 'COGS', 'Gross Profit', 'Expenses', 'Estimated Operating Profit'];
                $formattedRows = collect($data['breakdown'])->map(fn($b) => [
                    $b['date'],
                    $b['revenue'],
                    $b['cogs'],
                    $b['gross_profit'],
                    $b['expenses'],
                    $b['operating_profit'],
                ]);
                $filename = "orvik-profitability-{$period}-" . now()->format('Ymd') . ".csv";
                return $this->exportService->downloadCsv($filename, $headers, $formattedRows, $business, $user, 'profit');

            default:
                abort(404, 'Unknown export report type.');
        }
    }

    /**
     * Universal Excel XML Workbook Export Handler.
     */
    public function exportExcel(Request $request, string $type): StreamedResponse
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $user = Auth::user();
        $period = $request->get('period', 'this_month');

        switch ($type) {
            case 'sales':
                $this->authorizePermission('reports.sales');
                $rows = $this->salesReportService->getExportData($business, $request->all(), $period, $request->get('start_date'), $request->get('end_date'));
                $headers = ['Invoice Number', 'Date', 'Customer', 'Cashier', 'Subtotal', 'Discount', 'Tax', 'Total Amount', 'Payment Method', 'Status'];
                $formattedRows = $rows->map(fn($s) => [
                    $s->invoice_number,
                    $s->completed_at ? $s->completed_at->format('Y-m-d H:i') : '',
                    $s->customer ? $s->customer->name : 'Walk-in',
                    $s->user ? $s->user->name : '',
                    (float)$s->subtotal,
                    (float)$s->discount_amount,
                    (float)$s->tax_amount,
                    (float)$s->total_amount,
                    $s->payment_method,
                    $s->status,
                ]);
                return $this->exportService->downloadExcel("orvik-sales-{$period}.xls", 'Sales Report', $headers, $formattedRows, $business, $user, 'sales');

            case 'products':
                $this->authorizePermission('reports.products');
                $data = $this->productReportService->getReport($business, $request->all(), $period, $request->get('start_date'), $request->get('end_date'), 'revenue', 'desc', 1000);
                $headers = ['Product Name', 'SKU', 'Category', 'Units Sold', 'Revenue', 'COGS', 'Gross Profit', 'Margin %'];
                $formattedRows = $data['all_rows']->map(fn($p) => [
                    $p->product_name,
                    $p->sku ?: '',
                    $p->category_name,
                    $p->units_sold,
                    $p->revenue,
                    $p->cogs,
                    $p->gross_profit,
                    $p->margin . '%',
                ]);
                return $this->exportService->downloadExcel("orvik-products-{$period}.xls", 'Product Performance Report', $headers, $formattedRows, $business, $user, 'products');

            case 'inventory':
                $this->authorizePermission('reports.inventory');
                $data = $this->inventoryReportService->getInventoryStatusReport($business, $request->all(), 30, 1000);
                $headers = ['Product Name', 'SKU', 'Category', 'Current Stock', 'Threshold', 'Unit', 'Cost Price', 'Selling Price', 'Estimated Value', 'Status', 'Last Sold'];
                $formattedRows = $data['all_rows']->map(fn($p) => [
                    $p->name,
                    $p->sku ?: '',
                    $p->category_name,
                    $p->stock_quantity,
                    $p->low_stock_threshold,
                    $p->unit,
                    $p->cost_price,
                    $p->selling_price,
                    $p->stock_value,
                    $p->status,
                    $p->last_sold_date,
                ]);
                return $this->exportService->downloadExcel("orvik-inventory.xls", 'Inventory Status Report', $headers, $formattedRows, $business, $user, 'inventory');

            case 'profit':
                $this->authorizePermission('reports.profit');
                $data = $this->profitReportService->getReport($business, $period, $request->get('start_date'), $request->get('end_date'));
                $headers = ['Date', 'Revenue', 'COGS', 'Gross Profit', 'Expenses', 'Estimated Operating Profit'];
                $formattedRows = collect($data['breakdown'])->map(fn($b) => [
                    $b['date'],
                    $b['revenue'],
                    $b['cogs'],
                    $b['gross_profit'],
                    $b['expenses'],
                    $b['operating_profit'],
                ]);
                return $this->exportService->downloadExcel("orvik-profit-{$period}.xls", 'Profitability Report', $headers, $formattedRows, $business, $user, 'profit');

            default:
                abort(404, 'Unknown export report type.');
        }
    }

    /**
     * Printable Clean Report View.
     */
    public function printReport(Request $request, string $type): View
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $user = Auth::user();
        $period = $request->get('period', 'this_month');

        AuditLogger::log('report.printed', 'Report', null, ['report_type' => $type], $business->id, $user->id);

        switch ($type) {
            case 'sales':
                $this->authorizePermission('reports.sales');
                $data = $this->salesReportService->getReport($business, $request->all(), $period, $request->get('start_date'), $request->get('end_date'), 500);
                return view('app.reports.print', [
                    'business' => $business,
                    'title' => 'Sales Transactions Report',
                    'dateRange' => $data['dateRange'],
                    'headers' => ['Invoice', 'Date', 'Customer', 'Items', 'Total', 'Payment', 'Status'],
                    'rows' => $data['rows']->map(fn($s) => [
                        $s->invoice_number,
                        $s->completed_at ? $s->completed_at->format('M d, Y H:i') : '',
                        $s->customer ? $s->customer->name : 'Walk-in',
                        $s->items->count() . ' item(s)',
                        number_format($s->total_amount, 2),
                        ucfirst(str_replace('_', ' ', $s->payment_method)),
                        ucfirst($s->status),
                    ]),
                    'summary' => $data['summary'],
                ]);

            case 'inventory':
                $this->authorizePermission('reports.inventory');
                $data = $this->inventoryReportService->getInventoryStatusReport($business, $request->all(), 30, 500);
                return view('app.reports.print', [
                    'business' => $business,
                    'title' => 'Inventory Valuation & Status Report',
                    'dateRange' => ['label' => 'Current Valuation'],
                    'headers' => ['Product Name', 'SKU', 'Stock', 'Unit Cost', 'Selling Price', 'Stock Value', 'Status'],
                    'rows' => $data['all_rows']->map(fn($p) => [
                        $p->name,
                        $p->sku ?: '—',
                        $p->stock_quantity . ' ' . $p->unit,
                        number_format($p->cost_price, 2),
                        number_format($p->selling_price, 2),
                        number_format($p->stock_value, 2),
                        $p->status,
                    ]),
                    'summary' => $data['summary'],
                ]);

            case 'profit':
                $this->authorizePermission('reports.profit');
                $data = $this->profitReportService->getReport($business, $period, $request->get('start_date'), $request->get('end_date'));
                return view('app.reports.print', [
                    'business' => $business,
                    'title' => 'Executive Profitability Report',
                    'dateRange' => $data['dateRange'],
                    'headers' => ['Date', 'Revenue', 'COGS', 'Gross Profit', 'Expenses', 'Est. Operating Profit'],
                    'rows' => collect($data['breakdown'])->map(fn($b) => [
                        $b['date'],
                        number_format($b['revenue'], 2),
                        number_format($b['cogs'], 2),
                        number_format($b['gross_profit'], 2),
                        number_format($b['expenses'], 2),
                        number_format($b['operating_profit'], 2),
                    ]),
                    'summary' => [
                        'gross_profit' => $data['kpis']['gross_profit'],
                        'gross_margin' => $data['kpis']['gross_margin'] . '%',
                        'operating_profit' => $data['kpis']['operating_profit'],
                    ],
                ]);

            default:
                abort(404, 'Print view not supported for this report type.');
        }
    }
}
