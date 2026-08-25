<?php

namespace App\Services\Reports;

use App\Models\Business;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\DB;

class ProfitReportService
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    public function getReport(Business $business, string $period = 'this_month', ?string $customStart = null, ?string $customEnd = null): array
    {
        $timezone = $business->timezone ?? 'Asia/Karachi';
        $dateRange = $this->analyticsService->resolveDateRange($period, $customStart, $customEnd, $timezone);

        $kpis = $this->analyticsService->getKPIs($business, $dateRange, true);

        // Daily/Monthly Breakdown for the period
        $sales = Sale::where('business_id', $business->id)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('completed_at', [$dateRange['start'], $dateRange['end']])
            ->with(['items'])
            ->get();

        $startDateStr = $dateRange['start']->format('Y-m-d');
        $endDateStr = $dateRange['end']->format('Y-m-d');

        $expenses = Expense::where('business_id', $business->id)
            ->where('status', Expense::STATUS_ACTIVE)
            ->whereDate('expense_date', '>=', $startDateStr)
            ->whereDate('expense_date', '<=', $endDateStr)
            ->get();

        $periodDays = new \DatePeriod(
            $dateRange['start']->copy()->startOfDay(),
            new \DateInterval('P1D'),
            $dateRange['end']->copy()->endOfDay()
        );

        $breakdown = [];
        foreach ($periodDays as $dt) {
            $dateKey = $dt->format('Y-m-d');
            $breakdown[$dateKey] = [
                'date' => $dt->format('d M Y'),
                'revenue' => 0.0,
                'cogs' => 0.0,
                'gross_profit' => 0.0,
                'expenses' => 0.0,
                'operating_profit' => 0.0,
            ];
        }

        foreach ($sales as $sale) {
            $dateKey = $sale->completed_at->format('Y-m-d');
            if (isset($breakdown[$dateKey])) {
                $cogsForSale = (float)$sale->items->sum(function ($i) {
                    return (float)($i->cost_price ?? 0) * (float)$i->quantity;
                });
                $breakdown[$dateKey]['revenue'] += (float)$sale->total_amount;
                $breakdown[$dateKey]['cogs'] += $cogsForSale;
                $breakdown[$dateKey]['gross_profit'] += ((float)$sale->total_amount - $cogsForSale);
            }
        }

        foreach ($expenses as $expense) {
            $dateKey = $expense->expense_date ? $expense->expense_date->format('Y-m-d') : null;
            if ($dateKey && isset($breakdown[$dateKey])) {
                $breakdown[$dateKey]['expenses'] += (float)$expense->amount;
            }
        }

        foreach ($breakdown as &$row) {
            $row['operating_profit'] = $row['gross_profit'] - $row['expenses'];
        }

        krsort($breakdown);

        return [
            'kpis' => $kpis,
            'breakdown' => array_values($breakdown),
            'dateRange' => $dateRange,
        ];
    }
}
