<?php

namespace App\Services\Reports;

use App\Models\Business;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\AnalyticsService;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class SupplierReportService
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    public function getReport(Business $business, array $filters = [], string $period = 'all', ?string $customStart = null, ?string $customEnd = null, int $perPage = 25): array
    {
        $timezone = $business->timezone ?? 'Asia/Karachi';
        $dateRange = $this->analyticsService->resolveDateRange($period, $customStart, $customEnd, $timezone);

        $query = Supplier::where('business_id', $business->id)
            ->with(['purchases' => function ($q) use ($dateRange, $period) {
                $q->where('status', Purchase::STATUS_COMPLETED);
                if ($period !== 'all') {
                    $q->whereBetween('purchased_at', [$dateRange['start'], $dateRange['end']]);
                }
            }]);

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('company_name', 'like', $search)
                  ->orWhere('phone', 'like', $search);
            });
        }

        $suppliers = $query->get();

        $transformed = $suppliers->map(function ($sup) {
            $purchasesCount = $sup->purchases->count();
            $totalValue = (float)$sup->purchases->sum('total_amount');
            $paid = (float)$sup->purchases->where('payment_status', 'paid')->sum('total_amount');
            $unpaid = (float)$sup->purchases->whereIn('payment_status', ['unpaid', 'partial'])->sum('total_amount');
            $latest = $sup->purchases->sortByDesc('purchased_at')->first();

            return (object)[
                'id' => $sup->id,
                'name' => $sup->name,
                'company_name' => $sup->company_name,
                'phone' => $sup->phone,
                'purchases_count' => $purchasesCount,
                'total_purchases_value' => $totalValue,
                'paid_amount' => $paid,
                'unpaid_amount' => $unpaid,
                'last_purchase_date' => $latest ? $latest->purchased_at->format('d M Y') : 'Never',
            ];
        });

        $sorted = $transformed->sortByDesc('total_purchases_value');

        $totalSuppliers = $sorted->count();
        $totalProcurement = (float)$sorted->sum('total_purchases_value');

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
                'total_suppliers' => $totalSuppliers,
                'total_procurement_value' => $totalProcurement,
            ],
            'rows' => $paginated,
            'all_rows' => $sorted,
            'dateRange' => $dateRange,
            'filters' => $filters,
        ];
    }
}
