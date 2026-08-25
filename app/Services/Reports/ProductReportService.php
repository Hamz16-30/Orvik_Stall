<?php

namespace App\Services\Reports;

use App\Models\Business;
use App\Models\Sale;
use App\Services\AnalyticsService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductReportService
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Get Product Performance Report.
     */
    public function getReport(
        Business $business,
        array $filters = [],
        string $period = 'this_month',
        ?string $customStart = null,
        ?string $customEnd = null,
        string $sortBy = 'revenue',
        string $sortDirection = 'desc',
        int $perPage = 25
    ): array {
        $timezone = $business->timezone ?? 'Asia/Karachi';
        $dateRange = $this->analyticsService->resolveDateRange($period, $customStart, $customEnd, $timezone);

        $query = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('sales.business_id', $business->id)
            ->where('sales.status', Sale::STATUS_COMPLETED)
            ->whereBetween('sales.completed_at', [$dateRange['start'], $dateRange['end']]);

        if (!empty($filters['category_id']) && $filters['category_id'] !== 'all') {
            $query->where('products.category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('sale_items.product_name', 'like', $search)
                  ->orWhere('sale_items.sku', 'like', $search);
            });
        }

        $items = $query->select(
            'sale_items.product_name',
            'sale_items.sku',
            'sale_items.product_id',
            DB::raw('COALESCE(categories.name, "Uncategorized") as category_name'),
            DB::raw('SUM(sale_items.quantity) as units_sold'),
            DB::raw('SUM(sale_items.line_total) as revenue'),
            DB::raw('SUM(COALESCE(sale_items.cost_price, 0) * sale_items.quantity) as cogs')
        )
        ->groupBy('sale_items.product_name', 'sale_items.sku', 'sale_items.product_id', 'categories.name')
        ->get();

        // Calculate Gross Profit & Margin per item
        $transformed = $items->map(function ($item) {
            $revenue = (float)$item->revenue;
            $cogs = (float)$item->cogs;
            $profit = $revenue - $cogs;
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0.0;

            return (object)[
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'category_name' => $item->category_name,
                'units_sold' => (float)$item->units_sold,
                'revenue' => $revenue,
                'cogs' => $cogs,
                'gross_profit' => $profit,
                'margin' => $margin,
            ];
        });

        // Sorting
        $sorted = match ($sortBy) {
            'units' => $sortDirection === 'asc' ? $transformed->sortBy('units_sold') : $transformed->sortByDesc('units_sold'),
            'profit' => $sortDirection === 'asc' ? $transformed->sortBy('gross_profit') : $transformed->sortByDesc('gross_profit'),
            'margin' => $sortDirection === 'asc' ? $transformed->sortBy('margin') : $transformed->sortByDesc('margin'),
            'cogs' => $sortDirection === 'asc' ? $transformed->sortBy('cogs') : $transformed->sortByDesc('cogs'),
            default => $sortDirection === 'asc' ? $transformed->sortBy('revenue') : $transformed->sortByDesc('revenue'),
        };

        // Summary Calculations
        $totalUnits = (float)$transformed->sum('units_sold');
        $totalRevenue = (float)$transformed->sum('revenue');
        $totalCogs = (float)$transformed->sum('cogs');
        $totalProfit = $totalRevenue - $totalCogs;
        $avgMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0.0;

        // Manual Pagination for Collection
        $page = Paginator::resolveCurrentPage() ?: 1;
        $paginatedRows = new Paginator(
            $sorted->forPage($page, $perPage)->values(),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => request()->query()]
        );

        return [
            'summary' => [
                'total_products_sold' => $sorted->count(),
                'total_units' => $totalUnits,
                'total_revenue' => $totalRevenue,
                'total_cogs' => $totalCogs,
                'total_profit' => $totalProfit,
                'average_margin' => $avgMargin,
            ],
            'rows' => $paginatedRows,
            'all_rows' => $sorted,
            'dateRange' => $dateRange,
            'filters' => $filters,
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
        ];
    }

    /**
     * Get Category Performance Report.
     */
    public function getCategoryReport(Business $business, string $period = 'this_month', ?string $customStart = null, ?string $customEnd = null): array
    {
        $timezone = $business->timezone ?? 'Asia/Karachi';
        $dateRange = $this->analyticsService->resolveDateRange($period, $customStart, $customEnd, $timezone);

        $categories = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('sales.business_id', $business->id)
            ->where('sales.status', Sale::STATUS_COMPLETED)
            ->whereBetween('sales.completed_at', [$dateRange['start'], $dateRange['end']])
            ->select(
                DB::raw('COALESCE(categories.name, "Uncategorized") as category_name'),
                DB::raw('COUNT(DISTINCT sale_items.product_id) as product_count'),
                DB::raw('SUM(sale_items.quantity) as units_sold'),
                DB::raw('SUM(sale_items.line_total) as revenue'),
                DB::raw('SUM(COALESCE(sale_items.cost_price, 0) * sale_items.quantity) as cogs')
            )
            ->groupBy('category_name')
            ->orderByDesc('revenue')
            ->get();

        $transformed = $categories->map(function ($cat) {
            $rev = (float)$cat->revenue;
            $cogs = (float)$cat->cogs;
            $profit = $rev - $cogs;
            $margin = $rev > 0 ? round(($profit / $rev) * 100, 2) : 0.0;

            return (object)[
                'category_name' => $cat->category_name,
                'product_count' => (int)$cat->product_count,
                'units_sold' => (float)$cat->units_sold,
                'revenue' => $rev,
                'cogs' => $cogs,
                'gross_profit' => $profit,
                'margin' => $margin,
            ];
        });

        return [
            'rows' => $transformed,
            'dateRange' => $dateRange,
        ];
    }
}
