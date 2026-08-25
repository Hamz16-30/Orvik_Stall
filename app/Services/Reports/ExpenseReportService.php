<?php

namespace App\Services\Reports;

use App\Models\Business;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\AnalyticsService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExpenseReportService
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Get base query with filters applied.
     */
    protected function getBaseQuery(Business $business, array $filters, array $dateRange): Builder
    {
        $startDateStr = $dateRange['start']->format('Y-m-d');
        $endDateStr = $dateRange['end']->format('Y-m-d');

        $query = Expense::with(['category', 'user'])
            ->where('business_id', $business->id)
            ->whereDate('expense_date', '>=', $startDateStr)
            ->whereDate('expense_date', '<=', $endDateStr);

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        } else {
            $query->where('status', Expense::STATUS_ACTIVE);
        }

        if (!empty($filters['category_id']) && $filters['category_id'] !== 'all') {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['payment_method']) && $filters['payment_method'] !== 'all') {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', $search)
                  ->orWhere('reference', 'like', $search)
                  ->orWhere('notes', 'like', $search);
            });
        }

        return $query;
    }

    /**
     * Generate detailed expenses report.
     */
    public function getReport(Business $business, array $filters = [], string $period = 'this_month', ?string $customStart = null, ?string $customEnd = null, int $perPage = 25): array
    {
        $timezone = $business->timezone ?? 'Asia/Karachi';
        $dateRange = $this->analyticsService->resolveDateRange($period, $customStart, $customEnd, $timezone);

        $query = $this->getBaseQuery($business, $filters, $dateRange);

        $summaryQuery = clone $query;
        $totalExpenses = (float)(clone $summaryQuery)->sum('amount');
        $expenseCount = (int)(clone $summaryQuery)->count();
        $avgExpense = $expenseCount > 0 ? (float)bcdiv((string)$totalExpenses, (string)$expenseCount, 2) : 0.0;

        $rows = $query->orderByDesc('expense_date')->paginate($perPage)->withQueryString();

        // Category Breakdown
        $categoryBreakdown = $this->analyticsService->getExpenseBreakdown($business, $dateRange, 10);

        return [
            'summary' => [
                'total_expenses' => $totalExpenses,
                'expense_count' => $expenseCount,
                'average_expense' => $avgExpense,
                'category_breakdown' => $categoryBreakdown,
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

        return $this->getBaseQuery($business, $filters, $dateRange)->orderByDesc('expense_date')->get();
    }
}
