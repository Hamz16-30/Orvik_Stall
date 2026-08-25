<?php

namespace App\Services\SaaS;

use App\Models\Business;
use App\Models\Feature;
use App\Models\Plan;

class FeatureService
{
    /**
     * Determine if a business has access to a given feature flag.
     */
    public function canAccessFeature(Business $business, string $featureSlug): bool
    {
        if ($business->isSuspended()) {
            return false;
        }

        return $business->hasFeature($featureSlug);
    }

    /**
     * Get numerical limit value for a feature (null means unlimited).
     */
    public function getFeatureLimit(Business $business, string $featureSlug): ?float
    {
        if ($business->isSuspended()) {
            return 0.0;
        }

        return $business->featureLimit($featureSlug);
    }

    /**
     * Check if a specific usage value is within plan limit.
     */
    public function isWithinLimit(Business $business, string $featureSlug, float $currentUsage): bool
    {
        $limit = $this->getFeatureLimit($business, $featureSlug);

        // If limit is null, it's unlimited
        if ($limit === null) {
            return true;
        }

        return $currentUsage < $limit;
    }

    /**
     * Get complete summary of features and limits for business.
     */
    public function getPlanRestrictions(Business $business): array
    {
        $plan = $business->plan();
        $features = Feature::all();
        $restrictions = [];

        foreach ($features as $feature) {
            $restrictions[$feature->slug] = [
                'name' => $feature->name,
                'type' => $feature->type,
                'enabled' => $plan ? $plan->hasFeature($feature->slug) : false,
                'limit' => $plan ? $plan->getFeatureLimit($feature->slug) : 0,
            ];
        }

        return $restrictions;
    }
}
