<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\SaaS\PlatformAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlatformDashboardController extends Controller
{
    public function __construct(
        protected PlatformAnalyticsService $analyticsService
    ) {}

    public function index(Request $request): View
    {
        $period = $request->get('period', '30_days');
        $analytics = $this->analyticsService->getSummary($period);

        return view('platform.dashboard', [
            'user' => Auth::user(),
            'kpis' => $analytics['kpis'],
            'planDistribution' => $analytics['plan_distribution'],
            'recentBusinesses' => $analytics['recent_businesses'],
            'period' => $period,
        ]);
    }
}
