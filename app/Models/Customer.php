<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
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

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function getTotalOrdersAttribute(): int
    {
        return $this->sales()->where('status', Sale::STATUS_COMPLETED)->count();
    }

    public function getTotalSpentAttribute(): float
    {
        return (float)$this->sales()->where('status', Sale::STATUS_COMPLETED)->sum('total_amount');
    }

    public function getLastPurchaseDateAttribute(): ?string
    {
        $lastSale = $this->sales()->where('status', Sale::STATUS_COMPLETED)->latest('completed_at')->first();
        return $lastSale?->completed_at?->format('d M Y');
    }
}
