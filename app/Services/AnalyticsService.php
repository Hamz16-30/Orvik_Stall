<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Resolve start and end dates for the current period and equivalent previous comparison period.
     */
    public function resolveDateRange(string $period = 'today', ?string $customStart = null, ?string $customEnd = null, string $timezone = 'UTC'): array
    {
        $now = Carbon::now($timezone);

        switch ($period) {
            case 'yesterday':
                $start = $now->copy()->subDay()->startOfDay();
                $end = $now->copy()->subDay()->endOfDay();
                $prevStart = $start->copy()->subDay()->startOfDay();
                $prevEnd = $start->copy()->subDay()->endOfDay();
                $grouping = 'hourly';
                $label = 'Yesterday';
                break;

            case 'this_week':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                $prevStart = $start->copy()->subWeek();
                $prevEnd = $end->copy()->subWeek();
                $grouping = 'daily';
                $label = 'This Week';
                break;

            case 'this_month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $prevStart = $start->copy()->subMonth()->startOfMonth();
                $prevEnd = $start->copy()->subMonth()->endOfMonth();
                $grouping = 'daily';
                $label = 'This Month';
                break;

            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                $prevStart = $start->copy()->subMonth()->startOfMonth();
                $prevEnd = $start->copy()->subMonth()->endOfMonth();
                $grouping = 'daily';
                $label = 'Last Month';
                break;

            case 'custom':
                $start = $customStart ? Carbon::parse($customStart, $timezone)->startOfDay() : $now->copy()->startOfMonth();
                $end = $customEnd ? Carbon::parse($customEnd, $timezone)->endOfDay() : $now->copy()->endOfDay();
                $diffDays = $start->diffInDays($end) + 1;
                $prevStart = $start->copy()->subDays($diffDays);
                $prevEnd = $start->copy()->subSecond();
                $grouping = $diffDays <= 2 ? 'hourly' : 'daily';
                $label = $start->format('d M') . ' - ' . $end->format('d M');
                break;

            case 'today':
            default:
                $period = 'today';
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $start->copy()->subDay()->startOfDay();
                $prevEnd = $start->copy()->subDay()->endOfDay();
                $grouping = 'hourly';
                $label = 'Today';
                break;
        }

        return [
            'period' => $period,
            'label' => $label,
            'grouping' => $grouping,
            'start' => $start,
            'end' => $end,
            'prev_start' => $prevStart,
            'prev_end' => $prevEnd,
            'timezone' => $timezone,
        ];
    }

    /**
     * Compute Primary KPIs (Revenue, Orders, Gross Profit, Expenses, and Period Comparison % changes).
     */
    public function getKPIs(Business $business, array $dateRange, bool $canViewFinancials = true): array
    {
        // 1. Current Period Sales & Orders
        $currentSales = Sale::where('business_id', $business->id)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('completed_at', [$dateRange['start'], $dateRange['end']]);

        $revenue = (float)(clone $currentSales)->sum('total_amount');
        $ordersCount = (int)(clone $currentSales)->count();

        // 2. Previous Period Sales & Orders
        $prevSales = Sale::where('business_id', $business->id)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('completed_at', [$dateRange['prev_start'], $dateRange['prev_end']]);

        $prevRevenue = (float)(clone $prevSales)->sum('total_amount');
        $prevOrdersCount = (int)(clone $prevSales)->count();

        $revenueChange = $this->calculatePercentageChange($revenue, $prevRevenue);
        $ordersChange = $this->calculatePercentageChange($ordersCount, $prevOrdersCount);

        // 3. Financial Metrics (Gross Profit, Margin, Expenses, Operating Profit)
        $grossProfit = 0.0;
        $prevGrossProfit = 0.0;
        $grossProfitChange = null;
        $grossMargin = 0.0;
        $expenses = 0.0;
        $prevExpenses = 0.0;
        $expensesChange = null;
        $operatingProfit = 0.0;

        if ($canViewFinancials) {
            // Current COGS = SUM(sale_items.cost_price * quantity)
            $currentSaleIds = (clone $currentSales)->pluck('id');
            $cogs = (float)SaleItem::whereIn('sale_id', $currentSaleIds)
                ->whereNotNull('cost_price')
                ->sum(DB::raw('cost_price * quantity'));

            $grossProfit = $revenue - $cogs;
            $grossMargin = $revenue > 0 ? (float)bcmul(bcdiv((string)$grossProfit, (string)$revenue, 4), '100', 2) : 0.0;

            // Previous COGS
            $prevSaleIds = (clone $prevSales)->pluck('id');
            $prevCogs = (float)SaleItem::whereIn('sale_id', $prevSaleIds)
                ->whereNotNull('cost_price')
                ->sum(DB::raw('cost_price * quantity'));

            $prevGrossProfit = $prevRevenue - $prevCogs;
            $grossProfitChange = $this->calculatePercentageChange($grossProfit, $prevGrossProfit);

            // Current Expenses
            $startDateStr = $dateRange['start']->format('Y-m-d');
            $endDateStr = $dateRange['end']->format('Y-m-d');

            $currentExpensesQuery = Expense::where('business_id', $business->id)
                ->where('status', Expense::STATUS_ACTIVE)
                ->whereDate('expense_date', '>=', $startDateStr)
                ->whereDate('expense_date', '<=', $endDateStr);

            $expenses = (float)(clone $currentExpensesQuery)->sum('amount');

            // Previous Expenses
            $prevStartDateStr = $dateRange['prev_start']->format('Y-m-d');
            $prevEndDateStr = $dateRange['prev_end']->format('Y-m-d');

            $prevExpensesQuery = Expense::where('business_id', $business->id)
                ->where('status', Expense::STATUS_ACTIVE)
                ->whereDate('expense_date', '>=', $prevStartDateStr)
                ->whereDate('expense_date', '<=', $prevEndDateStr);

            $prevExpenses = (float)(clone $prevExpensesQuery)->sum('amount');
            $expensesChange = $this->calculatePercentageChange($expenses, $prevExpenses);

            $operatingProfit = $grossProfit - $expenses;
        }

        return [
            'revenue' => $revenue,
            'prev_revenue' => $prevRevenue,
            'revenue_change' => $revenueChange,
            'orders_count' => $ordersCount,
            'prev_orders_count' => $prevOrdersCount,
            'orders_change' => $ordersChange,
            'gross_profit' => $grossProfit,
            'prev_gross_profit' => $prevGrossProfit,
            'gross_profit_change' => $grossProfitChange,
            'gross_margin' => $grossMargin,
            'expenses' => $expenses,
            'prev_expenses' => $prevExpenses,
            'expenses_change' => $expensesChange,
            'operating_profit' => $operatingProfit,
        ];
    }

    /**
     * Compute Sales Trend data points for the trend chart.
     */
    public function getSalesTrend(Business $business, array $dateRange): array
    {
        $sales = Sale::where('business_id', $business->id)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('completed_at', [$dateRange['start'], $dateRange['end']])
            ->get();

        $labels = [];
        $values = [];
        $orders = [];

        if ($dateRange['grouping'] === 'hourly') {
            $slots = ['00:00', '03:00', '06:00', '09:00', '12:00', '15:00', '18:00', '21:00'];
            $slotData = array_fill_keys($slots, 0.0);
            $slotOrders = array_fill_keys($slots, 0);

            foreach ($sales as $sale) {
                $hour = (int)$sale->completed_at->format('H');
                $slotKey = '00:00';
                if ($hour >= 21) $slotKey = '21:00';
                elseif ($hour >= 18) $slotKey = '18:00';
                elseif ($hour >= 15) $slotKey = '15:00';
                elseif ($hour >= 12) $slotKey = '12:00';
                elseif ($hour >= 9) $slotKey = '09:00';
                elseif ($hour >= 6) $slotKey = '06:00';
                elseif ($hour >= 3) $slotKey = '03:00';

                $slotData[$slotKey] += (float)$sale->total_amount;
                $slotOrders[$slotKey] += 1;
            }

            $labels = array_keys($slotData);
            $values = array_values($slotData);
            $orders = array_values($slotOrders);
        } else {
            $period = new \DatePeriod(
                $dateRange['start']->copy()->startOfDay(),
                new \DateInterval('P1D'),
                $dateRange['end']->copy()->endOfDay()
            );

            $dayData = [];
            $dayOrders = [];
            foreach ($period as $dt) {
                $key = $dt->format('d M');
                $dayData[$key] = 0.0;
                $dayOrders[$key] = 0;
            }

            foreach ($sales as $sale) {
                $key = $sale->completed_at->format('d M');
                if (isset($dayData[$key])) {
                    $dayData[$key] += (float)$sale->total_amount;
                    $dayOrders[$key] += 1;
                }
            }

            $labels = array_keys($dayData);
            $values = array_values($dayData);
            $orders = array_values($dayOrders);
        }

        $maxValue = count($values) > 0 ? max($values) : 0;

        return [
            'labels' => $labels,
            'values' => $values,
            'orders' => $orders,
            'max_value' => $maxValue > 0 ? $maxValue : 100,
            'total_period_sales' => array_sum($values),
        ];
    }

    /**
     * Compute Top Selling Products (Hot Sellers).
     */
    public function getTopSellingProducts(Business $business, array $dateRange, int $limit = 5): Collection
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.business_id', $business->id)
            ->where('sales.status', Sale::STATUS_COMPLETED)
            ->whereBetween('sales.completed_at', [$dateRange['start'], $dateRange['end']])
            ->select(
                'sale_items.product_name',
                'sale_items.sku',
                DB::raw('SUM(sale_items.quantity) as total_units_sold'),
                DB::raw('SUM(sale_items.line_total) as total_revenue')
            )
            ->groupBy('sale_items.product_name', 'sale_items.sku')
            ->orderByDesc('total_units_sold')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Compute Inventory Health: Out of Stock, Low Stock, Dead Stock (30+ days without sales), and Never Sold.
     */
    public function getInventoryHealth(Business $business, int $deadStockDays = 30): array
    {
        $deadStockCutoff = Carbon::now()->subDays($deadStockDays);

        // 1. Out of Stock
        $outOfStock = Product::where('business_id', $business->id)
            ->where('status', 'active')
            ->where('track_inventory', true)
            ->where('stock_quantity', '<=', 0)
            ->orderBy('name')
            ->get();

        // 2. Low Stock (0 < stock <= threshold)
        $lowStock = Product::where('business_id', $business->id)
            ->where('status', 'active')
            ->where('track_inventory', true)
            ->where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->orderBy('stock_quantity')
            ->get();

        // 3. Dead Stock & Never Sold
        $trackedInStock = Product::where('business_id', $business->id)
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->with(['saleItems.sale'])
            ->get();

        $deadStock = collect();
        $neverSold = collect();

        foreach ($trackedInStock as $prod) {
            $latestSaleItem = $prod->saleItems->sortByDesc(function ($si) {
                return $si->sale?->completed_at ?? $si->created_at;
            })->first();

            if (!$latestSaleItem) {
                $neverSold->push([
                    'product' => $prod,
                    'stock' => (float)$prod->stock_quantity,
                    'unit' => $prod->unit,
                    'status' => 'Never Sold',
                ]);
            } else {
                $lastSoldAt = $latestSaleItem->sale?->completed_at ?? $latestSaleItem->created_at;
                if ($lastSoldAt && $lastSoldAt < $deadStockCutoff) {
                    $daysSinceSale = (int)$lastSoldAt->diffInDays(now());
                    $deadStock->push([
                        'product' => $prod,
                        'stock' => (float)$prod->stock_quantity,
                        'unit' => $prod->unit,
                        'last_sold_date' => $lastSoldAt->format('d M Y'),
                        'days_since_sale' => $daysSinceSale,
                    ]);
                }
            }
        }

        return [
            'out_of_stock' => $outOfStock,
            'out_of_stock_count' => $outOfStock->count(),
            'low_stock' => $lowStock,
            'low_stock_count' => $lowStock->count(),
            'dead_stock' => $deadStock->sortByDesc('days_since_sale')->values(),
            'dead_stock_count' => $deadStock->count(),
            'never_sold' => $neverSold->values(),
            'never_sold_count' => $neverSold->count(),
        ];
    }

    /**
     * Compute Actionable Business Attention Alerts.
     */
    public function getAttentionAlerts(Business $business, int $deadStockDays = 30): array
    {
        $health = $this->getInventoryHealth($business, $deadStockDays);
        $alerts = [];

        if ($health['out_of_stock_count'] > 0) {
            $alerts[] = [
                'type' => 'out_of_stock',
                'level' => 'critical',
                'title' => $health['out_of_stock_count'] . ' product(s) are completely Out of Stock',
                'description' => 'Customers cannot purchase these items. Ingest new stock shipments immediately.',
                'action_label' => 'Restock / Purchase',
                'action_route' => route('app.purchases.create'),
                'icon' => 'alert-octagon',
            ];
        }

        if ($health['low_stock_count'] > 0) {
            $alerts[] = [
                'type' => 'low_stock',
                'level' => 'warning',
                'title' => $health['low_stock_count'] . ' product(s) reached Low Stock threshold',
                'description' => 'Stock is running thin on fast-moving products. Review inventory levels.',
                'action_label' => 'View Low Stock',
                'action_route' => route('app.inventory.index', ['stock_level' => 'low_stock']),
                'icon' => 'alert-triangle',
            ];
        }

        if ($health['dead_stock_count'] > 0) {
            $alerts[] = [
                'type' => 'dead_stock',
                'level' => 'info',
                'title' => $health['dead_stock_count'] . " product(s) have not sold in {$deadStockDays}+ days",
                'description' => 'Capital is tied up in slow-moving items. Consider promotions or discounts.',
                'action_label' => 'View Inventory',
                'action_route' => route('app.inventory.index'),
                'icon' => 'clock',
            ];
        }

        return $alerts;
    }

    /**
     * Compute Breakdown by Payment Method.
     */
    public function getPaymentMethodBreakdown(Business $business, array $dateRange): array
    {
        $sales = Sale::where('business_id', $business->id)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('completed_at', [$dateRange['start'], $dateRange['end']])
            ->select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        $totalRevenue = $sales->sum('total');
        $methods = [];

        foreach ($sales as $s) {
            $pct = $totalRevenue > 0 ? round(($s->total / $totalRevenue) * 100, 1) : 0;
            $methods[] = [
                'method' => $s->payment_method,
                'label' => ucfirst(str_replace('_', ' ', $s->payment_method)),
                'total' => (float)$s->total,
                'count' => (int)$s->count,
                'percentage' => $pct,
            ];
        }

        return $methods;
    }

    /**
     * Compute Expense Category Breakdown.
     */
    public function getExpenseBreakdown(Business $business, array $dateRange, int $limit = 5): array
    {
        $startDateStr = $dateRange['start']->format('Y-m-d');
        $endDateStr = $dateRange['end']->format('Y-m-d');

        $expenses = Expense::where('expenses.business_id', $business->id)
            ->where('expenses.status', Expense::STATUS_ACTIVE)
            ->whereDate('expenses.expense_date', '>=', $startDateStr)
            ->whereDate('expenses.expense_date', '<=', $endDateStr)
            ->leftJoin('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->select(
                DB::raw('COALESCE(expense_categories.name, "Uncategorized") as category_name'),
                DB::raw('SUM(expenses.amount) as total_amount'),
                DB::raw('COUNT(expenses.id) as count')
            )
            ->groupBy('category_name')
            ->orderByDesc('total_amount')
            ->limit($limit)
            ->get();

        $totalExpenses = $expenses->sum('total_amount');
        $breakdown = [];

        foreach ($expenses as $e) {
            $pct = $totalExpenses > 0 ? round(($e->total_amount / $totalExpenses) * 100, 1) : 0;
            $breakdown[] = [
                'category' => $e->category_name,
                'total' => (float)$e->total_amount,
                'count' => (int)$e->count,
                'percentage' => $pct,
            ];
        }

        return $breakdown;
    }

    /**
     * Compute Customer Acquisition & Retention Metrics.
     */
    public function getCustomerMetrics(Business $business, array $dateRange): array
    {
        $totalCustomers = Customer::where('business_id', $business->id)->count();

        $newCustomers = Customer::where('business_id', $business->id)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->count();

        $returningCustomers = Customer::where('business_id', $business->id)
            ->has('sales', '>', 1)
            ->count();

        return [
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'returning_customers' => $returningCustomers,
        ];
    }

    /**
     * Compute Purchases Summary.
     */
    public function getPurchaseSummary(Business $business, array $dateRange): array
    {
        $purchases = Purchase::where('business_id', $business->id)
            ->where('status', Purchase::STATUS_COMPLETED)
            ->whereBetween('purchased_at', [$dateRange['start'], $dateRange['end']]);

        return [
            'total_amount' => (float)(clone $purchases)->sum('total_amount'),
            'count' => (int)(clone $purchases)->count(),
        ];
    }

    /**
     * Generate contextual time-based greeting.
     */
    public function getGreeting(string $userName, string $timezone = 'UTC'): string
    {
        $hour = (int)Carbon::now($timezone)->format('H');

        if ($hour < 12) {
            $salutation = 'Good morning';
        } elseif ($hour < 17) {
            $salutation = 'Good afternoon';
        } else {
            $salutation = 'Good evening';
        }

        return "{$salutation}, {$userName}";
    }

    /**
     * Calculate percentage change safely without division by zero.
     */
    protected function calculatePercentageChange(float $current, float $previous): ?array
    {
        if ($previous == 0) {
            return $current > 0 ? ['value' => 100, 'formatted' => '+100%', 'trend' => 'up', 'has_data' => true] : null;
        }

        $change = (($current - $previous) / $previous) * 100;
        $rounded = round($change, 1);

        return [
            'value' => $rounded,
            'formatted' => ($rounded >= 0 ? '+' : '') . $rounded . '%',
            'trend' => $rounded >= 0 ? 'up' : 'down',
            'has_data' => true,
        ];
    }
}
