<?php

namespace App\Services\Reports;

use App\Models\Business;
use App\Models\Sale;
use App\Services\AnalyticsService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PaymentReportService
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    public function getReport(Business $business, array $filters = [], string $period = 'this_month', ?string $customStart = null, ?string $customEnd = null, int $perPage = 25): array
    {
        $timezone = $business->timezone ?? 'Asia/Karachi';
        $dateRange = $this->analyticsService->resolveDateRange($period, $customStart, $customEnd, $timezone);

        $methodsBreakdown = $this->analyticsService->getPaymentMethodBreakdown($business, $dateRange);

        $query = Sale::with(['user', 'customer'])
            ->where('business_id', $business->id)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('completed_at', [$dateRange['start'], $dateRange['end']]);

        if (!empty($filters['payment_method']) && $filters['payment_method'] !== 'all') {
            $query->where('payment_method', $filters['payment_method']);
        }

        $totalRevenue = (float)(clone $query)->sum('total_amount');
        $totalTransactions = (int)(clone $query)->count();

        $rows = $query->orderByDesc('completed_at')->paginate($perPage)->withQueryString();

        return [
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_transactions' => $totalTransactions,
                'methods_breakdown' => $methodsBreakdown,
            ],
            'rows' => $rows,
            'dateRange' => $dateRange,
            'filters' => $filters,
        ];
    }
}
