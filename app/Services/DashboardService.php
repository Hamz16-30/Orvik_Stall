<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Sale;
use App\Models\User;

class DashboardService
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Build the complete data payload for the Stall Operations Dashboard.
     */
    public function getDashboardData(
        Business $business,
        User $user,
        string $period = 'today',
        ?string $customStart = null,
        ?string $customEnd = null,
        int $deadStockDays = 30
    ): array {
        $timezone = $business->timezone ?? 'Asia/Karachi';
        $dateRange = $this->analyticsService->resolveDateRange($period, $customStart, $customEnd, $timezone);

        $canViewFinancials = $user->isOwner() || $user->isManager();

        $kpis = $this->analyticsService->getKPIs($business, $dateRange, $canViewFinancials);
        $salesTrend = $this->analyticsService->getSalesTrend($business, $dateRange);
        $topProducts = $this->analyticsService->getTopSellingProducts($business, $dateRange, 5);
        $inventoryHealth = $this->analyticsService->getInventoryHealth($business, $deadStockDays);
        $attentionAlerts = $this->analyticsService->getAttentionAlerts($business, $deadStockDays);
        $paymentBreakdown = $this->analyticsService->getPaymentMethodBreakdown($business, $dateRange);
        $expenseBreakdown = $canViewFinancials ? $this->analyticsService->getExpenseBreakdown($business, $dateRange, 5) : [];
        $customerMetrics = $this->analyticsService->getCustomerMetrics($business, $dateRange);
        $purchaseSummary = $canViewFinancials ? $this->analyticsService->getPurchaseSummary($business, $dateRange) : ['total_amount' => 0, 'count' => 0];

        $greeting = $this->analyticsService->getGreeting($user->name, $timezone);

        $hasAnySales = Sale::where('business_id', $business->id)->exists();

        return [
            'business' => $business,
            'user' => $user,
            'period' => $period,
            'dateRange' => $dateRange,
            'canViewFinancials' => $canViewFinancials,
            'greeting' => $greeting,
            'kpis' => $kpis,
            'salesTrend' => $salesTrend,
            'topProducts' => $topProducts,
            'inventoryHealth' => $inventoryHealth,
            'attentionAlerts' => $attentionAlerts,
            'paymentBreakdown' => $paymentBreakdown,
            'expenseBreakdown' => $expenseBreakdown,
            'customerMetrics' => $customerMetrics,
            'purchaseSummary' => $purchaseSummary,
            'hasAnySales' => $hasAnySales,
        ];
    }
}
