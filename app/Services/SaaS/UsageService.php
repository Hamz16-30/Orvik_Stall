<?php

namespace App\Services\SaaS;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;

class UsageService
{
    public function __construct(
        protected FeatureService $featureService
    ) {}

    /**
     * Get usage metrics for a specific feature slug.
     */
    protected function calculateMetric(Business $business, string $featureSlug, int $currentCount): array
    {
        $limit = $this->featureService->getFeatureLimit($business, $featureSlug);
        $isUnlimited = $limit === null;
        $limitValue = $isUnlimited ? null : (int) $limit;

        if ($isUnlimited) {
            $percentage = 0;
            $warningLevel = 'normal';
            $canCreate = true;
        } else {
            $percentage = $limitValue > 0 ? min(100, (int) round(($currentCount / $limitValue) * 100)) : 100;

            if ($currentCount >= $limitValue) {
                $warningLevel = 'reached';
                $canCreate = false;
            } elseif ($percentage >= 90) {
                $warningLevel = 'critical';
                $canCreate = true;
            } elseif ($percentage >= 80) {
                $warningLevel = 'warning';
                $canCreate = true;
            } else {
                $warningLevel = 'normal';
                $canCreate = true;
            }
        }

        return [
            'used' => $currentCount,
            'limit' => $limitValue,
            'is_unlimited' => $isUnlimited,
            'percentage' => $percentage,
            'warning_level' => $warningLevel, // normal, warning, critical, reached
            'can_create' => $canCreate,
        ];
    }

    /**
     * Product usage calculation.
     */
    public function getProductUsage(Business $business): array
    {
        $count = Product::where('business_id', $business->id)->count();
        return $this->calculateMetric($business, 'products', $count);
    }

    public function canCreateProduct(Business $business): bool
    {
        return $this->getProductUsage($business)['can_create'];
    }

    /**
     * Staff/User usage calculation.
     */
    public function getUserUsage(Business $business): array
    {
        $count = $business->memberships()->where('status', '!=', 'deactivated')->count();
        return $this->calculateMetric($business, 'users', $count);
    }

    public function canCreateUser(Business $business): bool
    {
        return $this->getUserUsage($business)['can_create'];
    }

    /**
     * Monthly sales quota calculation.
     */
    public function getMonthlySalesUsage(Business $business): array
    {
        $startOfMonth = Carbon::now($business->timezone ?? 'Asia/Karachi')->startOfMonth();
        $count = Sale::where('business_id', $business->id)
            ->where('status', Sale::STATUS_COMPLETED)
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        return $this->calculateMetric($business, 'monthly_sales', $count);
    }

    public function canCompleteSale(Business $business): bool
    {
        return $this->getMonthlySalesUsage($business)['can_create'];
    }

    /**
     * Customers usage.
     */
    public function getCustomerUsage(Business $business): array
    {
        $count = Customer::where('business_id', $business->id)->count();
        return $this->calculateMetric($business, 'customers', $count);
    }

    public function canCreateCustomer(Business $business): bool
    {
        return $this->getCustomerUsage($business)['can_create'];
    }

    /**
     * Suppliers usage.
     */
    public function getSupplierUsage(Business $business): array
    {
        $count = Supplier::where('business_id', $business->id)->count();
        return $this->calculateMetric($business, 'suppliers', $count);
    }

    public function canCreateSupplier(Business $business): bool
    {
        return $this->getSupplierUsage($business)['can_create'];
    }

    /**
     * Complete usage bundle for dashboard & plan settings.
     */
    public function getAllUsage(Business $business): array
    {
        return [
            'products' => $this->getProductUsage($business),
            'users' => $this->getUserUsage($business),
            'monthly_sales' => $this->getMonthlySalesUsage($business),
            'customers' => $this->getCustomerUsage($business),
            'suppliers' => $this->getSupplierUsage($business),
        ];
    }
}
