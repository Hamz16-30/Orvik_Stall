<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    /**
     * Perform an atomic stock adjustment with row-level locking.
     */
    public function adjustStock(
        Product $product,
        string $type,
        float $quantity,
        ?string $reason,
        User $user
    ): StockMovement {
        if ($quantity <= 0 && $type !== StockMovement::TYPE_ADJUSTMENT_SET) {
            throw ValidationException::withMessages([
                'quantity' => 'Adjustment quantity must be greater than zero.',
            ]);
        }

        if ($type === StockMovement::TYPE_ADJUSTMENT_SET && $quantity < 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Stock quantity cannot be negative.',
            ]);
        }

        return DB::transaction(function () use ($product, $type, $quantity, $reason, $user) {
            /** @var Product $lockedProduct */
            $lockedProduct = Product::lockForUpdate()->findOrFail($product->id);

            $previousQuantity = (float)$lockedProduct->stock_quantity;
            $newQuantity = $previousQuantity;

            switch (strtoupper($type)) {
                case 'ADD':
                case StockMovement::TYPE_ADJUSTMENT_ADD:
                    $movementType = StockMovement::TYPE_ADJUSTMENT_ADD;
                    $newQuantity = $previousQuantity + $quantity;
                    break;

                case 'REMOVE':
                case StockMovement::TYPE_ADJUSTMENT_REMOVE:
                    $movementType = StockMovement::TYPE_ADJUSTMENT_REMOVE;
                    if ($previousQuantity - $quantity < 0) {
                        throw ValidationException::withMessages([
                            'quantity' => 'Cannot remove ' . $quantity . ' ' . $lockedProduct->unit . '(s). Current stock is only ' . $previousQuantity . '.',
                        ]);
                    }
                    $newQuantity = $previousQuantity - $quantity;
                    break;

                case 'SET':
                case StockMovement::TYPE_ADJUSTMENT_SET:
                    $movementType = StockMovement::TYPE_ADJUSTMENT_SET;
                    $newQuantity = $quantity;
                    break;

                default:
                    throw ValidationException::withMessages([
                        'type' => 'Invalid stock adjustment type: ' . $type,
                    ]);
            }

            // Update product stock
            $lockedProduct->update([
                'stock_quantity' => $newQuantity,
            ]);

            // Create immutable stock movement record
            $movement = StockMovement::create([
                'business_id' => $lockedProduct->business_id,
                'product_id' => $lockedProduct->id,
                'user_id' => $user->id,
                'type' => $movementType,
                'quantity' => $quantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $newQuantity,
                'reason' => $reason ?: 'Manual stock adjustment',
                'created_at' => now(),
            ]);

            // Audit log
            AuditLogger::log(
                action: 'stock.adjusted',
                entityType: Product::class,
                entityId: $lockedProduct->id,
                oldValues: ['stock_quantity' => $previousQuantity],
                newValues: ['stock_quantity' => $newQuantity, 'type' => $movementType, 'reason' => $reason],
                businessId: $lockedProduct->business_id,
                userId: $user->id
            );

            return $movement;
        });
    }

    /**
     * Compute real-time inventory summary statistics for a business.
     */
    public function getSummary(Business $business): array
    {
        $products = Product::where('business_id', $business->id)->where('status', 'active');

        $totalProducts = (clone $products)->count();

        // Low stock count: stock_quantity > 0 AND stock_quantity <= low_stock_threshold
        $lowStockCount = (clone $products)
            ->where('track_inventory', true)
            ->where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();

        // Out of stock count: stock_quantity <= 0
        $outOfStockCount = (clone $products)
            ->where('track_inventory', true)
            ->where('stock_quantity', '<=', 0)
            ->count();

        // Inventory valuation: SUM(stock_quantity * cost_price) where cost_price is not null and stock > 0
        $valuation = (clone $products)
            ->where('track_inventory', true)
            ->whereNotNull('cost_price')
            ->where('stock_quantity', '>', 0)
            ->sum(DB::raw('stock_quantity * cost_price'));

        return [
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'inventory_value' => (float)$valuation,
        ];
    }

    /**
     * Get filtered, tenant-scoped products query with pagination.
     */
    public function getFilteredProducts(Business $business, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Product::with('category')->where('business_id', $business->id);

        // Status Filter (active vs archived)
        $status = $filters['status'] ?? 'active';
        if ($status === 'archived') {
            $query->onlyTrashed();
        } else {
            $query->where('status', 'active');
        }

        // Search Query (name, sku, barcode)
        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('sku', 'like', $search)
                  ->orWhere('barcode', 'like', $search);
            });
        }

        // Category Filter
        if (!empty($filters['category_id']) && $filters['category_id'] !== 'all') {
            $query->where('category_id', $filters['category_id']);
        }

        // Stock Status Filter
        if (!empty($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'in_stock':
                    $query->where('track_inventory', true)
                          ->whereColumn('stock_quantity', '>', 'low_stock_threshold');
                    break;
                case 'low_stock':
                    $query->where('track_inventory', true)
                          ->where('stock_quantity', '>', 0)
                          ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
                    break;
                case 'out_of_stock':
                    $query->where('track_inventory', true)
                          ->where('stock_quantity', '<=', 0);
                    break;
            }
        }

        // Sorting
        $sort = $filters['sort'] ?? 'recent';
        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('selling_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('selling_price', 'desc');
                break;
            case 'stock_asc':
                $query->orderBy('stock_quantity', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('stock_quantity', 'desc');
                break;
            case 'recent':
            default:
                $query->orderByDesc('id');
                break;
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
