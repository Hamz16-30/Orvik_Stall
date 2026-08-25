<?php

namespace App\Traits;

use App\Models\Business;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    /**
     * Boot the BelongsToTenant trait.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            if (!$model->business_id && app(TenantContext::class)->hasCurrentBusiness()) {
                $model->business_id = app(TenantContext::class)->getCurrentBusinessId();
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app(TenantContext::class)->hasCurrentBusiness()) {
                $builder->where($builder->getModel()->getTable() . '.business_id', app(TenantContext::class)->getCurrentBusinessId());
            }
        });
    }

    /**
     * Relationship to Business.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
