<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Carbon;

class FinanceService
{
    /**
     * Compute comprehensive financial metrics for a specific business and date range.
     */
    public function getFinancialOverview(Business $business, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        // 1. Sales Revenue
        $salesQuery = Sale::where('business_id', $business->id)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('completed_at', [$startDate, $endDate]);

        $totalRevenue = (float)(clone $salesQuery)->sum('total_amount');
        $totalOrders = (int)(clone $salesQuery)->count();

        // 2. Cost of Goods Sold (COGS)
        $saleIds = (clone $salesQuery)->pluck('id');
        $cogs = (float)SaleItem::whereIn('sale_id', $saleIds)
            ->whereNotNull('cost_price')
            ->sum(\DB::raw('cost_price * quantity'));

        // 3. Gross Profit = Revenue - COGS
        $grossProfit = $totalRevenue - $cogs;

        // 4. Operating Expenses
        $expensesQuery = Expense::where('business_id', $business->id)
            ->where('status', Expense::STATUS_ACTIVE)
            ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $totalExpenses = (float)(clone $expensesQuery)->sum('amount');

        // 5. Operating Profit / Net Margin = Gross Profit - Operating Expenses
        $operatingProfit = $grossProfit - $totalExpenses;

        // 6. Inventory Purchases (Cash Outflow)
        $purchasesQuery = Purchase::where('business_id', $business->id)
            ->where('status', Purchase::STATUS_COMPLETED)
            ->whereBetween('purchased_at', [$startDate, $endDate]);

        $totalPurchases = (float)(clone $purchasesQuery)->sum('total_amount');

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'total_expenses' => $totalExpenses,
            'operating_profit' => $operatingProfit,
            'total_purchases' => $totalPurchases,
        ];
    }
}
