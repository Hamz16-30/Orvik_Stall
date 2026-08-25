<?php

namespace App\Services;

use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TenantContext
{
    protected ?Business $currentBusiness = null;

    /**
     * Get the active business for the authenticated session.
     */
    public function getCurrentBusiness(): ?Business
    {
        if ($this->currentBusiness) {
            return $this->currentBusiness;
        }

        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $sessionBusinessId = Session::get('active_business_id');

        if ($sessionBusinessId) {
            // Verify user is an active member of this business
            $business = $user->businesses()
                ->where('businesses.id', $sessionBusinessId)
                ->wherePivot('status', 'active')
                ->where('businesses.status', 'active')
                ->first();

            if ($business) {
                $this->currentBusiness = $business;
                return $this->currentBusiness;
            }
        }

        // Fallback to user's first active business
        $business = $user->businesses()
            ->wherePivot('status', 'active')
            ->where('businesses.status', 'active')
            ->first();

        if ($business) {
            $this->setCurrentBusiness($business);
            return $this->currentBusiness;
        }

        return null;
    }

    /**
     * Set the current business in context and session.
     */
    public function setCurrentBusiness(Business $business): void
    {
        $this->currentBusiness = $business;
        Session::put('active_business_id', $business->id);
    }

    /**
     * Check if a business is currently active.
     */
    public function hasCurrentBusiness(): bool
    {
        return $this->getCurrentBusiness() !== null;
    }

    /**
     * Get current business ID.
     */
    public function getCurrentBusinessId(): ?int
    {
        return $this->getCurrentBusiness()?->id;
    }

    /**
     * Clear context.
     */
    public function clear(): void
    {
        $this->currentBusiness = null;
        Session::forget('active_business_id');
    }
}
