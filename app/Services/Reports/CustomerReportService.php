<?php

namespace App\Services\Reports;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Sale;
use App\Services\AnalyticsService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerReportService
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Get Customer Performance Report.
     */
    public function getReport(
        Business $business,
        array $filters = [],
        string $period = 'all',
        ?string $customStart = null,
        ?string $customEnd = null,
        string $sortBy = 'spent',
        string $sortDirection = 'desc',
        int $perPage = 25
    ): array {
        $timezone = $business->timezone ?? 'Asia/Karachi';
        $dateRange = $this->analyticsService->resolveDateRange($period, $customStart, $customEnd, $timezone);

        $query = Customer::where('business_id', $business->id)
            ->with(['sales' => function ($q) use ($dateRange, $period) {
                $q->where('status', Sale::STATUS_COMPLETED);
                if ($period !== 'all') {
                    $q->whereBetween('completed_at', [$dateRange['start'], $dateRange['end']]);
                }
            }]);

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('phone', 'like', $search)
                  ->orWhere('email', 'like', $search);
            });
        }

        $customers = $query->get();

        $transformed = $customers->map(function ($cust) {
            $orders = $cust->sales->count();
            $spent = (float)$cust->sales->sum('total_amount');
            $aov = $orders > 0 ? (float)bcdiv((string)$spent, (string)$orders, 2) : 0.0;
            $latestSale = $cust->sales->sortByDesc('completed_at')->first();

            return (object)[
                'id' => $cust->id,
                'name' => $cust->name,
                'phone' => $cust->phone,
                'email' => $cust->email,
                'created_at' => $cust->created_at,
                'orders_count' => $orders,
                'total_spent' => $spent,
                'average_order_value' => $aov,
                'last_purchase_date' => $latestSale ? $latestSale->completed_at->format('d M Y') : 'Never',
            ];
        });

        // Sorting
        $sorted = match ($sortBy) {
            'orders' => $sortDirection === 'asc' ? $transformed->sortBy('orders_count') : $transformed->sortByDesc('orders_count'),
            'name' => $sortDirection === 'asc' ? $transformed->sortBy('name') : $transformed->sortByDesc('name'),
            default => $sortDirection === 'asc' ? $transformed->sortBy('total_spent') : $transformed->sortByDesc('total_spent'),
        };

        // Summary
        $totalCustomers = $sorted->count();
        $totalRevenueFromCustomers = (float)$sorted->sum('total_spent');
        $activeOrderingCustomers = $sorted->where('orders_count', '>', 0)->count();

        $page = Paginator::resolveCurrentPage() ?: 1;
        $paginated = new Paginator(
            $sorted->forPage($page, $perPage)->values(),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => request()->query()]
        );

        return [
            'summary' => [
                'total_customers' => $totalCustomers,
                'active_customers' => $activeOrderingCustomers,
                'total_customer_revenue' => $totalRevenueFromCustomers,
            ],
            'rows' => $paginated,
            'all_rows' => $sorted,
            'dateRange' => $dateRange,
            'filters' => $filters,
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
        ];
    }
}
