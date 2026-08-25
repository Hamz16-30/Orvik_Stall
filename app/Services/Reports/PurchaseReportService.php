<?php

namespace App\Services\Reports;

use App\Models\Business;
use App\Models\Purchase;
use App\Services\AnalyticsService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PurchaseReportService
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Get base query with filters applied.
     */
    protected function getBaseQuery(Business $business, array $filters, array $dateRange): Builder
    {
        $query = Purchase::with(['supplier', 'user', 'items'])
            ->where('business_id', $business->id)
            ->whereBetween('purchased_at', [$dateRange['start'], $dateRange['end']]);

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        } else {
            $query->where('status', Purchase::STATUS_COMPLETED);
        }

        if (!empty($filters['supplier_id']) && $filters['supplier_id'] !== 'all') {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', $search)
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', $search)->orWhere('company_name', 'like', $search);
                  });
            });
        }

        return $query;
    }

    /**
     * Generate detailed purchase report.
     */
    public function getReport(Business $business, array $filters = [], string $period = 'this_month', ?string $customStart = null, ?string $customEnd = null, int $perPage = 25): array
    {
        $timezone = $business->timezone ?? 'Asia/Karachi';
        $dateRange = $this->analyticsService->resolveDateRange($period, $customStart, $customEnd, $timezone);

        $query = $this->getBaseQuery($business, $filters, $dateRange);

        $summaryQuery = clone $query;
        $totalPurchases = (float)(clone $summaryQuery)->sum('total_amount');
        $purchaseCount = (int)(clone $summaryQuery)->count();
        $paidPurchases = (float)(clone $summaryQuery)->where('payment_status', 'paid')->sum('total_amount');
        $unpaidPurchases = (float)(clone $summaryQuery)->whereIn('payment_status', ['unpaid', 'partial'])->sum('total_amount');

        $rows = $query->orderByDesc('purchased_at')->paginate($perPage)->withQueryString();

        return [
            'summary' => [
                'total_purchases_value' => $totalPurchases,
                'purchase_count' => $purchaseCount,
                'paid_purchases_value' => $paidPurchases,
                'unpaid_purchases_value' => $unpaidPurchases,
            ],
            'rows' => $rows,
            'dateRange' => $dateRange,
            'filters' => $filters,
        ];
    }

    public function getExportData(Business $business, array $filters = [], string $period = 'this_month', ?string $customStart = null, ?string $customEnd = null): Collection
    {
        $timezone = $business->timezone ?? 'Asia/Karachi';
        $dateRange = $this->analyticsService->resolveDateRange($period, $customStart, $customEnd, $timezone);

        return $this->getBaseQuery($business, $filters, $dateRange)->orderByDesc('purchased_at')->get();
    }
}
