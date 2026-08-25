<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use BelongsToTenant;

    const UPDATED_AT = null; // Append-only model

    const TYPE_OPENING = 'OPENING';
    const TYPE_ADJUSTMENT_ADD = 'ADJUSTMENT_ADD';
    const TYPE_ADJUSTMENT_REMOVE = 'ADJUSTMENT_REMOVE';
    const TYPE_ADJUSTMENT_SET = 'ADJUSTMENT_SET';
    const TYPE_SALE = 'SALE';
    const TYPE_SALE_RETURN = 'SALE_RETURN';
    const TYPE_PURCHASE = 'PURCHASE';
    const TYPE_PURCHASE_VOID = 'PURCHASE_VOID';

    protected $fillable = [
        'business_id',
        'product_id',
        'user_id',
        'type',
        'quantity',
        'previous_quantity',
        'new_quantity',
        'reason',
        'reference_type',
        'reference_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'previous_quantity' => 'decimal:3',
            'new_quantity' => 'decimal:3',
            'created_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getReferenceAttribute(): ?string
    {
        if ($this->reason) {
            return $this->reason;
        }
        if ($this->reference_type && $this->reference_id) {
            return class_basename($this->reference_type) . ' #' . $this->reference_id;
        }
        return null;
    }
}
