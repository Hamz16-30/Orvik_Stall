<?php

namespace App\Services\Reports;

use App\Models\Business;
use App\Models\Sale;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Get base query with filters applied.
     */
    protected function getBaseQuery(Business $business, array $filters, array $dateRange): Builder
    {
        $query = Sale::with(['user', 'customer', 'items'])
            ->where('sales.business_id', $business->id)
            ->whereBetween('sales.completed_at', [$dateRange['start'], $dateRange['end']]);

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('sales.status', $filters['status']);
        } else {
            // Default to completed sales unless specified
            $query->where('sales.status', Sale::STATUS_COMPLETED);
        }

        if (!empty($filters['payment_method']) && $filters['payment_method'] !== 'all') {
            $query->where('sales.payment_method', $filters['payment_method']);
        }

        if (!empty($filters['user_id']) && $filters['user_id'] !== 'all') {
            $query->where('sales.user_id', $filters['user_id']);
        }

        if (!empty($filters['customer_id']) && $filters['customer_id'] !== 'all') {
            $query->where('sales.customer_id', $filters['customer_id']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('sales.invoice_number', 'like', $search)
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', $search)->orWhere('phone', 'like', $search);
                  });
            });
        }

        return $query;
    }

    /**
     * Generate detailed sales report with summary and pagination.
     */
    public function getReport(Business $business, array $filters = [], string $period = 'this_month', ?string $customStart = null, ?string $customEnd = null, int $perPage = 25): array
    {
        $timezone = $business->timezone ?? 'Asia/Karachi';
        $dateRange = $this->analyticsService->resolveDateRange($period, $customStart, $customEnd, $timezone);

        $query = $this->getBaseQuery($business, $filters, $dateRange);

        // Compute summary metrics on completed sales
        $summaryQuery = clone $query;
        $grossSales = (float)(clone $summaryQuery)->sum('subtotal');
        $discounts = (float)(clone $summaryQuery)->sum('discount_amount');
        $taxes = (float)(clone $summaryQuery)->sum('tax_amount');
        $netSales = (float)(clone $summaryQuery)->sum('total_amount');
        $ordersCount = (int)(clone $summaryQuery)->count();
        $averageOrderValue = $ordersCount > 0 ? (float)bcdiv((string)$netSales, (string)$ordersCount, 2) : 0.0;

        $rows = $query->orderByDesc('completed_at')->paginate($perPage)->withQueryString();

        return [
            'summary' => [
                'gross_sales' => $grossSales,
                'discounts' => $discounts,
                'taxes' => $taxes,
                'net_sales' => $netSales,
                'orders_count' => $ordersCount,
                'average_order_value' => $averageOrderValue,
            ],
            'rows' => $rows,
            'dateRange' => $dateRange,
            'filters' => $filters,
        ];
    }

    /**
     * Get grouped summary (Daily, Weekly, Monthly).
     */
    public function getGroupedReport(Business $business, string $grouping = 'daily', string $period = 'this_month', ?string $customStart = null, ?string $customEnd = null): array
    {
        $timezone = $business->timezone ?? 'Asia/Karachi';
        $dateRange = $this->analyticsService->resolveDateRange($period, $customStart, $customEnd, $timezone);

        $sales = Sale::where('business_id', $business->id)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('completed_at', [$dateRange['start'], $dateRange['end']])
            ->with(['items'])
            ->orderBy('completed_at')
            ->get();

        $grouped = [];

        foreach ($sales as $sale) {
            $dt = $sale->completed_at;
            if ($grouping === 'monthly') {
                $groupKey = $dt->format('Y-m');
                $label = $dt->format('F Y');
            } elseif ($grouping === 'weekly') {
                $groupKey = $dt->format('Y-W');
                $label = 'Week ' . $dt->format('W, Y') . ' (' . $dt->copy()->startOfWeek()->format('d M') . ' - ' . $dt->copy()->endOfWeek()->format('d M') . ')';
            } else {
                $groupKey = $dt->format('Y-m-d');
                $label = $dt->format('l, d M Y');
            }

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'key' => $groupKey,
                    'label' => $label,
                    'orders_count' => 0,
                    'gross_sales' => 0.0,
                    'discounts' => 0.0,
                    'taxes' => 0.0,
                    'net_sales' => 0.0,
                    'cogs' => 0.0,
                    'gross_profit' => 0.0,
                ];
            }

            $cogsForSale = (float)$sale->items->sum(function ($i) {
                return (float)($i->cost_price ?? 0) * (float)$i->quantity;
            });

            $grouped[$groupKey]['orders_count'] += 1;
            $grouped[$groupKey]['gross_sales'] += (float)$sale->subtotal;
            $grouped[$groupKey]['discounts'] += (float)$sale->discount_amount;
            $grouped[$groupKey]['taxes'] += (float)$sale->tax_amount;
            $grouped[$groupKey]['net_sales'] += (float)$sale->total_amount;
            $grouped[$groupKey]['cogs'] += $cogsForSale;
            $grouped[$groupKey]['gross_profit'] += ((float)$sale->total_amount - $cogsForSale);
        }

        krsort($grouped);

        return [
            'grouping' => $grouping,
            'rows' => array_values($grouped),
            'dateRange' => $dateRange,
        ];
    }

    /**
     * Get non-paginated rows for Export.
     */
    public function getExportData(Business $business, array $filters = [], string $period = 'this_month', ?string $customStart = null, ?string $customEnd = null): Collection
    {
        $timezone = $business->timezone ?? 'Asia/Karachi';
        $dateRange = $this->analyticsService->resolveDateRange($period, $customStart, $customEnd, $timezone);

        return $this->getBaseQuery($business, $filters, $dateRange)
            ->orderByDesc('completed_at')
            ->get();
    }
}
