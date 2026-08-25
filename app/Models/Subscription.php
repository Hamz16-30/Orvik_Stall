<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    const STATUS_TRIALING = 'TRIALING';
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_PAST_DUE = 'PAST_DUE';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_EXPIRED = 'EXPIRED';
    const STATUS_SUSPENDED = 'SUSPENDED';

    protected $fillable = [
        'business_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'cancel_at_period_end',
        'provider',
        'provider_subscription_id',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'cancel_at_period_end' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(BillingInvoice::class);
    }

    public function isTrialing(): bool
    {
        return $this->status === self::STATUS_TRIALING && ($this->trial_ends_at === null || $this->trial_ends_at->isFuture());
    }

    public function isActive(): bool
    {
        if ($this->status === self::STATUS_ACTIVE) {
            return $this->ends_at === null || $this->ends_at->isFuture();
        }

        return $this->isTrialing();
    }

    public function isPastDue(): bool
    {
        return $this->status === self::STATUS_PAST_DUE;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED || $this->cancelled_at !== null;
    }

    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }

        if ($this->status === self::STATUS_TRIALING && $this->trial_ends_at && $this->trial_ends_at->isPast()) {
            return true;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return true;
        }

        return false;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function daysLeftInTrial(): int
    {
        if (!$this->isTrialing() || !$this->trial_ends_at) {
            return 0;
        }

        return max(0, (int) ceil(now()->diffInDays($this->trial_ends_at, false)));
    }
}
