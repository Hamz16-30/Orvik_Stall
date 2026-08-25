<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\SaaS\UsageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductService
{
    /**
     * Create a new product and optionally record its initial opening stock movement.
     */
    public function createProduct(array $data, Business $business, User $user): Product
    {
        $usageService = app(UsageService::class);
        if (!$usageService->canCreateProduct($business)) {
            $usage = $usageService->getProductUsage($business);
            throw ValidationException::withMessages([
                'plan_limit' => "You have reached your plan's maximum allowed limit of {$usage['limit']} products. Upgrade your plan to continue adding products.",
            ]);
        }

        return DB::transaction(function () use ($data, $business, $user) {
            $openingStock = isset($data['opening_stock']) ? (float)$data['opening_stock'] : 0.0;

            $product = Product::create([
                'business_id' => $business->id,
                'category_id' => $data['category_id'] ?? null,
                'name' => $data['name'],
                'sku' => $data['sku'] ?? null,
                'barcode' => $data['barcode'] ?? null,
                'description' => $data['description'] ?? null,
                'cost_price' => isset($data['cost_price']) && $data['cost_price'] !== '' ? $data['cost_price'] : null,
                'selling_price' => $data['selling_price'],
                'stock_quantity' => $openingStock,
                'low_stock_threshold' => $data['low_stock_threshold'] ?? 5.0,
                'unit' => $data['unit'] ?? 'piece',
                'status' => 'active',
                'track_inventory' => isset($data['track_inventory']) ? (bool)$data['track_inventory'] : true,
            ]);

            // If initial stock was provided, record OPENING stock movement
            if ($openingStock > 0 && $product->track_inventory) {
                StockMovement::create([
                    'business_id' => $business->id,
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'type' => StockMovement::TYPE_OPENING,
                    'quantity' => $openingStock,
                    'previous_quantity' => 0.0,
                    'new_quantity' => $openingStock,
                    'reason' => 'Initial opening stock on product creation',
                    'created_at' => now(),
                ]);
            }

            AuditLogger::log(
                action: 'product.created',
                entityType: Product::class,
                entityId: $product->id,
                newValues: ['name' => $product->name, 'selling_price' => $product->selling_price, 'stock_quantity' => $product->stock_quantity],
                businessId: $business->id,
                userId: $user->id
            );

            return $product;
        });
    }

    /**
     * Update product details (excluding stock quantity, which is strictly managed via stock adjustments).
     */
    public function updateProduct(Product $product, array $data, User $user): Product
    {
        $oldValues = $product->only(['name', 'category_id', 'sku', 'barcode', 'cost_price', 'selling_price', 'low_stock_threshold', 'unit', 'description', 'track_inventory']);

        $product->update([
            'category_id' => $data['category_id'] ?? null,
            'name' => $data['name'],
            'sku' => $data['sku'] ?? null,
            'barcode' => $data['barcode'] ?? null,
            'description' => $data['description'] ?? null,
            'cost_price' => isset($data['cost_price']) && $data['cost_price'] !== '' ? $data['cost_price'] : null,
            'selling_price' => $data['selling_price'],
            'low_stock_threshold' => $data['low_stock_threshold'] ?? 5.0,
            'unit' => $data['unit'] ?? 'piece',
            'track_inventory' => isset($data['track_inventory']) ? (bool)$data['track_inventory'] : true,
        ]);

        AuditLogger::log(
            action: 'product.updated',
            entityType: Product::class,
            entityId: $product->id,
            oldValues: $oldValues,
            newValues: $data,
            businessId: $product->business_id,
            userId: $user->id
        );

        return $product;
    }

    /**
     * Archive (soft-delete) a product.
     */
    public function archiveProduct(Product $product, User $user): void
    {
        $product->delete();

        AuditLogger::log(
            action: 'product.archived',
            entityType: Product::class,
            entityId: $product->id,
            oldValues: ['name' => $product->name],
            businessId: $product->business_id,
            userId: $user->id
        );
    }

    /**
     * Restore an archived product.
     */
    public function restoreProduct(Product $product, User $user): void
    {
        $product->restore();

        AuditLogger::log(
            action: 'product.restored',
            entityType: Product::class,
            entityId: $product->id,
            newValues: ['name' => $product->name],
            businessId: $product->business_id,
            userId: $user->id
        );
    }
}
