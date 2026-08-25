<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;

    const SLUG_FREE = 'free';
    const SLUG_STARTER = 'starter';
    const SLUG_GROWTH = 'growth';
    const SLUG_PRO = 'pro';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'yearly_price',
        'billing_interval',
        'currency',
        'is_active',
        'is_public',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->withPivot('enabled', 'limit_value')
            ->withTimestamps();
    }

    public function isFree(): bool
    {
        return (float) $this->price === 0.00;
    }

    public function getFeatureLimit(string $featureSlug): ?float
    {
        $feature = $this->features()->where('slug', $featureSlug)->first();
        if (!$feature || !$feature->pivot->enabled) {
            return 0.0;
        }

        return $feature->pivot->limit_value !== null ? (float) $feature->pivot->limit_value : null; // null represents unlimited
    }

    public function hasFeature(string $featureSlug): bool
    {
        $feature = $this->features()->where('slug', $featureSlug)->first();
        return $feature ? (bool) $feature->pivot->enabled : false;
    }
}
