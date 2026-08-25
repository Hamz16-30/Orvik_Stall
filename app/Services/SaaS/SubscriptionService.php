<?php

namespace App\Services\SaaS;

use App\Models\Business;
use App\Models\BusinessSuspension;
use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function __construct(
        protected UsageService $usageService
    ) {}

    /**
     * Start standard trial on registration.
     */
    public function startTrial(Business $business, ?Plan $plan = null, ?int $trialDays = null): Subscription
    {
        if (!$plan) {
            $defaultSlug = PlatformSetting::get('default_plan', Plan::SLUG_STARTER);
            $plan = Plan::where('slug', $defaultSlug)->first() ?? Plan::first();
            if (!$plan) {
                $plan = Plan::create([
                    'name' => 'Starter',
                    'slug' => Plan::SLUG_STARTER,
                    'price' => 1999,
                    'billing_interval' => 'monthly',
                    'currency' => 'PKR',
                    'is_active' => true,
                ]);
            }
        }

        $trialDays = $trialDays ?? (int) PlatformSetting::get('default_trial_days', 14);
        $trialEndsAt = Carbon::now()->addDays($trialDays);

        $subscription = Subscription::create([
            'business_id' => $business->id,
            'plan_id' => $plan->id,
            'status' => Subscription::STATUS_TRIALING,
            'trial_ends_at' => $trialEndsAt,
            'starts_at' => Carbon::now(),
            'ends_at' => $trialEndsAt,
            'provider' => 'manual',
        ]);

        AuditLogger::log(
            action: 'subscription.trial_started',
            entityType: 'Subscription',
            entityId: $subscription->id,
            newValues: [
                'plan' => $plan->name,
                'trial_days' => $trialDays,
                'trial_ends_at' => $trialEndsAt->toIso8601String(),
            ],
            businessId: $business->id,
            userId: $business->owner_id
        );

        return $subscription;
    }

    /**
     * Change plan (upgrade / downgrade).
     */
    public function changePlan(Business $business, Plan $newPlan, string $billingInterval = 'monthly', string $provider = 'manual'): array
    {
        $currentSub = $business->currentSubscription();
        $oldPlanName = $currentSub?->plan?->name ?? 'None';

        // Check if downgrading exceeds usage limits
        $warnings = [];
        $productUsage = $this->usageService->getProductUsage($business);
        $newProductLimit = $newPlan->getFeatureLimit('products');

        if ($newProductLimit !== null && $productUsage['used'] > $newProductLimit) {
            $warnings[] = "You currently have {$productUsage['used']} products. The {$newPlan->name} plan allows {$newProductLimit}. Existing products will remain safe, but you cannot add new products until usage is below the plan limit.";
        }

        $userUsage = $this->usageService->getUserUsage($business);
        $newUserLimit = $newPlan->getFeatureLimit('users');
        if ($newUserLimit !== null && $userUsage['used'] > $newUserLimit) {
            $warnings[] = "You currently have {$userUsage['used']} staff users. The {$newPlan->name} plan allows {$newUserLimit}.";
        }

        $durationMonths = $billingInterval === 'yearly' ? 12 : 1;
        $endsAt = Carbon::now()->addMonths($durationMonths);

        $status = $newPlan->isFree() ? Subscription::STATUS_ACTIVE : Subscription::STATUS_ACTIVE;

        DB::transaction(function () use ($business, $currentSub, $newPlan, $status, $endsAt, $provider) {
            // Cancel older subscriptions
            if ($currentSub && $currentSub->status !== Subscription::STATUS_CANCELLED) {
                $currentSub->update([
                    'status' => Subscription::STATUS_CANCELLED,
                    'cancelled_at' => Carbon::now(),
                ]);
            }

            // Create new active subscription
            Subscription::create([
                'business_id' => $business->id,
                'plan_id' => $newPlan->id,
                'status' => $status,
                'starts_at' => Carbon::now(),
                'ends_at' => $endsAt,
                'provider' => $provider,
            ]);
        });

        $updatedSub = $business->fresh()->currentSubscription();

        AuditLogger::log(
            action: 'subscription.plan_changed',
            entityType: 'Subscription',
            entityId: $updatedSub->id,
            newValues: [
                'old_plan' => $oldPlanName,
                'new_plan' => $newPlan->name,
                'billing_interval' => $billingInterval,
                'provider' => $provider,
            ],
            businessId: $business->id,
            userId: $business->owner_id
        );

        return [
            'subscription' => $updatedSub,
            'warnings' => $warnings,
        ];
    }

    /**
     * Cancel subscription.
     */
    public function cancelSubscription(Business $business, bool $atPeriodEnd = true): Subscription
    {
        $subscription = $business->currentSubscription();
        if (!$subscription) {
            abort(404, 'No active subscription found.');
        }

        if ($atPeriodEnd) {
            $subscription->update([
                'cancel_at_period_end' => true,
                'cancelled_at' => Carbon::now(),
            ]);
        } else {
            $subscription->update([
                'status' => Subscription::STATUS_CANCELLED,
                'cancelled_at' => Carbon::now(),
            ]);
        }

        AuditLogger::log(
            action: 'subscription.cancelled',
            entityType: 'Subscription',
            entityId: $subscription->id,
            newValues: [
                'cancel_at_period_end' => $atPeriodEnd,
                'ends_at' => $subscription->ends_at?->toIso8601String(),
            ],
            businessId: $business->id,
            userId: $business->owner_id
        );

        return $subscription;
    }

    /**
     * Reactivate a cancelled subscription before period end.
     */
    public function reactivateSubscription(Business $business): Subscription
    {
        $subscription = $business->currentSubscription();
        if (!$subscription) {
            abort(404, 'No subscription found to reactivate.');
        }

        $subscription->update([
            'cancel_at_period_end' => false,
            'status' => Subscription::STATUS_ACTIVE,
            'cancelled_at' => null,
        ]);

        AuditLogger::log(
            action: 'subscription.reactivated',
            entityType: 'Subscription',
            entityId: $subscription->id,
            businessId: $business->id,
            userId: $business->owner_id
        );

        return $subscription;
    }

    /**
     * Suspend business account (Platform admin action).
     */
    public function suspendBusiness(Business $business, string $reason, User $admin): BusinessSuspension
    {
        return DB::transaction(function () use ($business, $reason, $admin) {
            $business->update(['status' => 'suspended']);

            $currentSub = $business->currentSubscription();
            if ($currentSub) {
                $currentSub->update(['status' => Subscription::STATUS_SUSPENDED]);
            }

            $suspension = BusinessSuspension::create([
                'business_id' => $business->id,
                'reason' => $reason,
                'suspended_by' => $admin->id,
                'suspended_at' => Carbon::now(),
            ]);

            AuditLogger::log(
                action: 'business.suspended',
                entityType: 'Business',
                entityId: $business->id,
                newValues: [
                    'reason' => $reason,
                    'admin_name' => $admin->name,
                ],
                businessId: $business->id,
                userId: $admin->id
            );

            return $suspension;
        });
    }

    /**
     * Reactivate/Unsuspend business account.
     */
    public function unsuspendBusiness(Business $business, User $admin): Business
    {
        return DB::transaction(function () use ($business, $admin) {
            $business->update(['status' => 'active']);

            $currentSub = $business->currentSubscription();
            if ($currentSub && $currentSub->status === Subscription::STATUS_SUSPENDED) {
                $currentSub->update(['status' => Subscription::STATUS_ACTIVE]);
            }

            $lastSuspension = BusinessSuspension::where('business_id', $business->id)
                ->whereNull('unsuspended_at')
                ->latest()
                ->first();

            if ($lastSuspension) {
                $lastSuspension->update([
                    'unsuspended_by' => $admin->id,
                    'unsuspended_at' => Carbon::now(),
                ]);
            }

            AuditLogger::log(
                action: 'business.reactivated',
                entityType: 'Business',
                entityId: $business->id,
                newValues: [
                    'admin_name' => $admin->name,
                ],
                businessId: $business->id,
                userId: $admin->id
            );

            return $business;
        });
    }
}
