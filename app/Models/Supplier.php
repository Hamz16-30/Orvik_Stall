<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'company_name',
        'phone',
        'email',
        'address',
        'notes',
        'status',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function getTotalPurchasesAttribute(): int
    {
        return $this->purchases()->where('status', Purchase::STATUS_COMPLETED)->count();
    }

    public function getTotalSpentAttribute(): float
    {
        return (float)$this->purchases()->where('status', Purchase::STATUS_COMPLETED)->sum('total_amount');
    }

    public function getLastPurchaseDateAttribute(): ?string
    {
        $last = $this->purchases()->where('status', Purchase::STATUS_COMPLETED)->latest('purchased_at')->first();
        return $last?->purchased_at?->format('d M Y');
    }
}
