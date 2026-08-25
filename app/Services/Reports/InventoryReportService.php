<?php

namespace App\Services\Reports;

use App\Models\Business;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class InventoryReportService
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Get Complete Inventory Status Report.
     */
    public function getInventoryStatusReport(Business $business, array $filters = [], int $deadStockDays = 30, int $perPage = 25): array
    {
        $query = Product::with(['category', 'saleItems.sale'])
            ->where('business_id', $business->id)
            ->where('status', 'active');

        if (!empty($filters['category_id']) && $filters['category_id'] !== 'all') {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('sku', 'like', $search)
                  ->orWhere('barcode', 'like', $search);
            });
        }

        $deadStockCutoff = Carbon::now()->subDays($deadStockDays);
        $products = $query->orderBy('name')->get();

        $rows = $products->map(function ($prod) use ($deadStockCutoff) {
            $stock = (float)$prod->stock_quantity;
            $threshold = (float)$prod->low_stock_threshold;
            $cost = (float)($prod->cost_price ?? 0);
            $stockValue = $stock * $cost;

            $latestSaleItem = $prod->saleItems->sortByDesc(function ($si) {
                return $si->sale?->completed_at ?? $si->created_at;
            })->first();

            $lastSoldAt = $latestSaleItem ? ($latestSaleItem->sale?->completed_at ?? $latestSaleItem->created_at) : null;

            if ($stock <= 0) {
                $status = 'OUT OF STOCK';
                $statusVariant = 'destructive';
            } elseif ($stock <= $threshold) {
                $status = 'LOW STOCK';
                $statusVariant = 'warning';
            } elseif (!$lastSoldAt) {
                $status = 'NEVER SOLD';
                $statusVariant = 'secondary';
            } elseif ($lastSoldAt < $deadStockCutoff) {
                $status = 'DEAD STOCK';
                $statusVariant = 'secondary';
            } else {
                $status = 'IN STOCK';
                $statusVariant = 'in-stock';
            }

            return (object)[
                'id' => $prod->id,
                'name' => $prod->name,
                'sku' => $prod->sku,
                'category_name' => $prod->category ? $prod->category->name : 'Uncategorized',
                'stock_quantity' => $stock,
                'low_stock_threshold' => $threshold,
                'unit' => $prod->unit,
                'cost_price' => $cost,
                'selling_price' => (float)$prod->selling_price,
                'stock_value' => $stockValue,
                'status' => $status,
                'status_variant' => $statusVariant,
                'last_sold_date' => $lastSoldAt ? $lastSoldAt->format('d M Y') : 'Never',
                'days_since_sale' => $lastSoldAt ? (int)$lastSoldAt->diffInDays(now()) : null,
            ];
        });

        // Filter by specific stock status if requested
        if (!empty($filters['stock_status']) && $filters['stock_status'] !== 'all') {
            $statusTarget = strtoupper(str_replace('_', ' ', $filters['stock_status']));
            $rows = $rows->filter(fn($r) => $r->status === $statusTarget)->values();
        }

        $totalValuation = (float)$rows->sum('stock_value');
        $totalItemsCount = $rows->count();
        $outOfStockCount = $rows->where('status', 'OUT OF STOCK')->count();
        $lowStockCount = $rows->where('status', 'LOW STOCK')->count();
        $deadStockCount = $rows->where('status', 'DEAD STOCK')->count();

        // Manual Pagination for the collection
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => request()->query()]
        );

        return [
            'summary' => [
                'total_products' => $totalItemsCount,
                'total_valuation' => $totalValuation,
                'out_of_stock' => $outOfStockCount,
                'low_stock' => $lowStockCount,
                'dead_stock' => $deadStockCount,
            ],
            'rows' => $paginated,
            'all_rows' => $rows,
            'filters' => $filters,
        ];
    }

    /**
     * Get Stock Movements Audit Report.
     */
    public function getStockMovementsReport(Business $business, array $filters = [], string $period = 'this_month', ?string $customStart = null, ?string $customEnd = null, int $perPage = 25): array
    {
        $timezone = $business->timezone ?? 'Asia/Karachi';
        $dateRange = $this->analyticsService->resolveDateRange($period, $customStart, $customEnd, $timezone);

        $query = StockMovement::with(['product', 'user'])
            ->where('business_id', $business->id)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);

        if (!empty($filters['movement_type']) && $filters['movement_type'] !== 'all') {
            $query->where('type', $filters['movement_type']);
        }

        if (!empty($filters['product_id']) && $filters['product_id'] !== 'all') {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', $search)
                  ->orWhere('notes', 'like', $search)
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', $search)->orWhere('sku', 'like', $search);
                  });
            });
        }

        $summaryQuery = clone $query;
        $totalIn = (float)(clone $summaryQuery)->where('quantity', '>', 0)->sum('quantity');
        $totalOut = (float)(clone $summaryQuery)->where('quantity', '<', 0)->sum('quantity');
        $totalCount = (int)(clone $summaryQuery)->count();

        $rows = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        return [
            'summary' => [
                'total_movements' => $totalCount,
                'total_stock_in' => $totalIn,
                'total_stock_out' => abs($totalOut),
            ],
            'rows' => $rows,
            'dateRange' => $dateRange,
            'filters' => $filters,
        ];
    }
}
