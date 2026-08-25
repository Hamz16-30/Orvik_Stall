<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected TenantContext $tenantContext
    ) {}

    public function index(Request $request): View
    {
        $business = $this->tenantContext->getCurrentBusiness();
        $user = Auth::user();

        $period = $request->get('period', 'today');
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');
        $deadStockDays = (int)$request->get('dead_stock_days', 30);

        $dashboardData = $this->dashboardService->getDashboardData(
            $business,
            $user,
            $period,
            $customStart,
            $customEnd,
            $deadStockDays
        );

        return view('app.dashboard', $dashboardData);
    }
}
