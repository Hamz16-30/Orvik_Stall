<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToTenant, SoftDeletes;

    const STATUS_IN_STOCK = 'IN_STOCK';
    const STATUS_LOW_STOCK = 'LOW_STOCK';
    const STATUS_OUT_OF_STOCK = 'OUT_OF_STOCK';

    protected $fillable = [
        'business_id',
        'category_id',
        'name',
        'sku',
        'barcode',
        'description',
        'cost_price',
        'selling_price',
        'stock_quantity',
        'low_stock_threshold',
        'unit',
        'status',
        'track_inventory',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock_quantity' => 'decimal:3',
            'low_stock_threshold' => 'decimal:3',
            'track_inventory' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->orderByDesc('created_at');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Derived Stock Status based on quantity and threshold.
     */
    public function getStockStatusAttribute(): string
    {
        if (!$this->track_inventory) {
            return self::STATUS_IN_STOCK;
        }

        $stock = (float)$this->stock_quantity;
        $threshold = (float)$this->low_stock_threshold;

        if ($stock <= 0) {
            return self::STATUS_OUT_OF_STOCK;
        }

        if ($stock <= $threshold) {
            return self::STATUS_LOW_STOCK;
        }

        return self::STATUS_IN_STOCK;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_status === self::STATUS_LOW_STOCK;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->stock_status === self::STATUS_OUT_OF_STOCK;
    }

    /**
     * Computed inventory valuation for this product (stock * cost).
     */
    public function getInventoryValueAttribute(): float
    {
        if ($this->cost_price === null) {
            return 0.0;
        }

        return (float)bcmul((string)$this->stock_quantity, (string)$this->cost_price, 2);
    }
}
