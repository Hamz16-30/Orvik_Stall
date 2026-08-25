<?php

namespace App\Services\SaaS;

use App\Models\Business;
use App\Models\Plan;
use App\Models\Sale;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PlatformAnalyticsService
{
    /**
     * Complete SaaS Platform Analytics Summary.
     */
    public function getSummary(string $period = '30_days'): array
    {
        $now = Carbon::now();

        // 1. Business Counts
        $totalBusinesses = Business::count();
        $activeBusinesses = Business::where('status', 'active')->count();
        $suspendedBusinesses = Business::where('status', 'suspended')->count();

        // 2. Subscriptions Classification
        $activeSubscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)->count();
        $trialSubscriptions = Subscription::where('status', Subscription::STATUS_TRIALING)->count();
        $pastDueSubscriptions = Subscription::where('status', Subscription::STATUS_PAST_DUE)->count();
        $cancelledSubscriptions = Subscription::where('status', Subscription::STATUS_CANCELLED)->count();
        $expiredSubscriptions = Subscription::where('status', Subscription::STATUS_EXPIRED)->count();

        // 3. User Counts
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();

        // 4. Financial SaaS Metrics (MRR / ARR / ARPU)
        // Calculate MRR from active paid subscriptions
        $paidSubs = Subscription::with('plan')
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereHas('plan', fn($q) => $q->where('price', '>', 0))
            ->get();

        $mrr = 0.0;
        foreach ($paidSubs as $sub) {
            $mrr += (float) ($sub->plan?->price ?? 0);
        }

        $arr = $mrr * 12;
        $paidCount = $paidSubs->count();
        $arpu = $paidCount > 0 ? round($mrr / $paidCount, 2) : 0.0;

        // 5. GMV (Total Volume Processed across all businesses)
        $totalVolumeProcessed = (float) Sale::where('status', Sale::STATUS_COMPLETED)->sum('total_amount');
        $totalSalesCount = Sale::where('status', Sale::STATUS_COMPLETED)->count();

        // 6. Growth Velocity
        $newBusinesses7Days = Business::where('created_at', '>=', $now->copy()->subDays(7))->count();
        $newBusinesses30Days = Business::where('created_at', '>=', $now->copy()->subDays(30))->count();

        // 7. Plan Distribution
        $plans = Plan::withCount(['subscriptions' => function ($query) {
            $query->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIALING]);
        }])->get();

        $planDistribution = $plans->map(fn($p) => [
            'name' => $p->name,
            'slug' => $p->slug,
            'price' => (float) $p->price,
            'count' => $p->subscriptions_count,
            'share_percentage' => $totalBusinesses > 0 ? round(($p->subscriptions_count / $totalBusinesses) * 100, 1) : 0,
        ]);

        // 8. Conversion Rate
        $endedTrialsCount = Subscription::whereIn('status', [
            Subscription::STATUS_EXPIRED,
            Subscription::STATUS_ACTIVE,
            Subscription::STATUS_CANCELLED,
        ])->whereNotNull('trial_ends_at')->count();

        $convertedTrialsCount = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->whereNotNull('trial_ends_at')
            ->whereHas('plan', fn($q) => $q->where('price', '>', 0))
            ->count();

        $conversionRate = $endedTrialsCount > 0 ? round(($convertedTrialsCount / $endedTrialsCount) * 100, 1) : 0.0;

        return [
            'kpis' => [
                'total_businesses' => $totalBusinesses,
                'active_businesses' => $activeBusinesses,
                'suspended_businesses' => $suspendedBusinesses,
                'trial_accounts' => $trialSubscriptions,
                'paid_subscriptions' => $paidCount,
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'mrr' => $mrr,
                'arr' => $arr,
                'arpu' => $arpu,
                'total_sales_count' => $totalSalesCount,
                'gmv' => $totalVolumeProcessed,
                'new_businesses_7d' => $newBusinesses7Days,
                'new_businesses_30d' => $newBusinesses30Days,
                'conversion_rate' => $conversionRate,
                'churn_count' => $cancelledSubscriptions,
            ],
            'plan_distribution' => $planDistribution,
            'recent_businesses' => Business::with(['owner', 'subscriptions.plan'])
                ->latest()
                ->take(6)
                ->get(),
        ];
    }
}
