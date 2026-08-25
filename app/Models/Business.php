<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Business extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'email',
        'phone',
        'address',
        'currency',
        'timezone',
        'status',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($business) {
            if (!$business->slug) {
                $slug = Str::slug($business->name);
                $count = static::where('slug', 'like', "{$slug}%")->count();
                $business->slug = $count ? "{$slug}-{$count}" : $slug;
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_user')
            ->withPivot('id', 'role_id', 'status', 'joined_at')
            ->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(BusinessUser::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(BillingInvoice::class);
    }

    public function suspensions(): HasMany
    {
        return $this->hasMany(BusinessSuspension::class);
    }

    public function currentSubscription(): ?Subscription
    {
        // Active or trialing subscription
        return $this->subscriptions()
            ->with('plan.features')
            ->whereIn('status', [
                Subscription::STATUS_ACTIVE,
                Subscription::STATUS_TRIALING,
                Subscription::STATUS_PAST_DUE,
                Subscription::STATUS_CANCELLED,
            ])
            ->latest('id')
            ->first() ?? $this->subscriptions()->with('plan.features')->latest('id')->first();
    }

    public function plan(): ?Plan
    {
        return $this->currentSubscription()?->plan;
    }

    public function hasFeature(string $featureSlug): bool
    {
        // Suspended businesses cannot access features
        if ($this->isSuspended()) {
            return false;
        }

        $plan = $this->plan();
        if (!$plan) {
            return true;
        }

        return $plan->hasFeature($featureSlug);
    }

    public function featureLimit(string $featureSlug): ?float
    {
        if ($this->isSuspended()) {
            return 0.0;
        }

        $plan = $this->plan();
        if (!$plan) {
            return null;
        }

        return $plan->getFeatureLimit($featureSlug);
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended' || $this->currentSubscription()?->status === Subscription::STATUS_SUSPENDED;
    }

    public function isOwner(User $user): bool
    {
        return (int)$this->owner_id === (int)$user->id;
    }
}
