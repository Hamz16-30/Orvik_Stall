<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function generatePurchaseNumber(Business $business, string $prefix = 'PUR'): string
    {
        $count = Purchase::where('business_id', $business->id)->count();
        $sequence = $count + 1;
        do {
            $purchaseNumber = sprintf('%s-%06d', $prefix, $sequence);
            $exists = Purchase::where('business_id', $business->id)
                ->where('purchase_number', $purchaseNumber)
                ->exists();

            if ($exists) {
                $sequence++;
            }
        } while ($exists);

        return $purchaseNumber;
    }

    public function createPurchase(array $data, Business $business, User $user): Purchase
    {
        $items = $data['items'] ?? [];
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Purchase must contain at least one product line item.',
            ]);
        }

        return DB::transaction(function () use ($data, $business, $user, $items) {
            $subtotal = '0.00';
            $validatedItems = [];

            foreach ($items as $item) {
                $productId = $item['product_id'];
                $quantity = (float)($item['quantity'] ?? 1);
                $unitCost = (float)($item['unit_cost'] ?? 0);

                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Item quantity must be greater than zero.',
                    ]);
                }
                if ($unitCost < 0) {
                    throw ValidationException::withMessages([
                        'unit_cost' => 'Unit cost cannot be negative.',
                    ]);
                }

                /** @var Product|null $product */
                $product = Product::lockForUpdate()->find($productId);

                if (!$product || (int)$product->business_id !== (int)$business->id) {
                    throw ValidationException::withMessages([
                        'items' => 'One or more selected products do not belong to this business.',
                    ]);
                }

                $lineTotal = bcmul((string)$unitCost, (string)$quantity, 2);
                $subtotal = bcadd($subtotal, $lineTotal, 2);

                $validatedItems[] = [
                    'product' => $product,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'line_total' => (float)$lineTotal,
                ];
            }

            $discountAmount = isset($data['discount_amount']) ? (float)$data['discount_amount'] : 0.0;
            if ($discountAmount < 0) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Discount cannot be negative.',
                ]);
            }
            if ($discountAmount > (float)$subtotal) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Discount cannot exceed purchase subtotal.',
                ]);
            }

            $taxAmount = isset($data['tax_amount']) ? (float)$data['tax_amount'] : 0.0;
            $totalAmount = (float)bcsub((string)$subtotal, (string)$discountAmount, 2);
            $totalAmount = (float)bcadd((string)$totalAmount, (string)$taxAmount, 2);

            $purchaseNumber = $this->generatePurchaseNumber($business);

            $purchase = Purchase::create([
                'business_id' => $business->id,
                'supplier_id' => $data['supplier_id'] ?? null,
                'user_id' => $user->id,
                'purchase_number' => $purchaseNumber,
                'subtotal' => (float)$subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'payment_status' => $data['payment_status'] ?? 'paid',
                'status' => Purchase::STATUS_COMPLETED,
                'notes' => $data['notes'] ?? null,
                'purchased_at' => $data['purchased_at'] ?? now(),
            ]);

            // Create Purchase Items and Ingest Stock
            foreach ($validatedItems as $vItem) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $vItem['product']->id,
                    'product_name' => $vItem['product_name'],
                    'sku' => $vItem['sku'],
                    'quantity' => $vItem['quantity'],
                    'unit_cost' => $vItem['unit_cost'],
                    'line_total' => $vItem['line_total'],
                ]);

                // Increase inventory and update latest cost price
                $prevStock = (float)$vItem['product']->stock_quantity;
                $newStock = $prevStock + $vItem['quantity'];

                $vItem['product']->update([
                    'stock_quantity' => $newStock,
                    'cost_price' => $vItem['unit_cost'], // Update current product cost to latest purchase cost
                ]);

                StockMovement::create([
                    'business_id' => $business->id,
                    'product_id' => $vItem['product']->id,
                    'user_id' => $user->id,
                    'type' => StockMovement::TYPE_PURCHASE,
                    'quantity' => $vItem['quantity'],
                    'previous_quantity' => $prevStock,
                    'new_quantity' => $newStock,
                    'reason' => 'Inventory Purchase #' . $purchaseNumber,
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'created_at' => now(),
                ]);
            }

            AuditLogger::log(
                action: 'purchase.created',
                entityType: Purchase::class,
                entityId: $purchase->id,
                newValues: [
                    'purchase_number' => $purchase->purchase_number,
                    'total_amount' => $purchase->total_amount,
                    'items_count' => count($validatedItems),
                ],
                businessId: $business->id,
                userId: $user->id
            );

            return $purchase->load(['items', 'supplier']);
        });
    }

    public function voidPurchase(Purchase $purchase, string $reason, User $user): Purchase
    {
        if ($purchase->status !== Purchase::STATUS_COMPLETED) {
            throw ValidationException::withMessages([
                'purchase' => 'Only completed purchases can be voided.',
            ]);
        }

        return DB::transaction(function () use ($purchase, $reason, $user) {
            $purchase->update([
                'status' => Purchase::STATUS_VOIDED,
                'void_reason' => $reason,
                'voided_by' => $user->id,
                'voided_at' => now(),
            ]);

            // Reverse inventory for each item
            foreach ($purchase->items as $item) {
                if ($item->product_id) {
                    /** @var Product|null $product */
                    $product = Product::lockForUpdate()->find($item->product_id);
                    if ($product) {
                        $prevStock = (float)$product->stock_quantity;
                        $newStock = $prevStock - (float)$item->quantity;

                        $product->update(['stock_quantity' => $newStock]);

                        StockMovement::create([
                            'business_id' => $purchase->business_id,
                            'product_id' => $product->id,
                            'user_id' => $user->id,
                            'type' => StockMovement::TYPE_PURCHASE_VOID,
                            'quantity' => -(float)$item->quantity,
                            'previous_quantity' => $prevStock,
                            'new_quantity' => $newStock,
                            'reason' => 'Voided Purchase #' . $purchase->purchase_number . ': ' . $reason,
                            'reference_type' => 'purchase_void',
                            'reference_id' => $purchase->id,
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            AuditLogger::log(
                action: 'purchase.voided',
                entityType: Purchase::class,
                entityId: $purchase->id,
                oldValues: ['status' => 'completed'],
                newValues: ['status' => 'voided', 'reason' => $reason],
                businessId: $purchase->business_id,
                userId: $user->id
            );

            return $purchase;
        });
    }

    public function getFiltered(Business $business, array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Purchase::with(['supplier', 'items'])
            ->where('business_id', $business->id);

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', $search)
                  ->orWhere('notes', 'like', $search)
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', $search)->orWhere('company_name', 'like', $search);
                  });
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['supplier_id']) && $filters['supplier_id'] !== 'all') {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        // Date Range
        $dateFilter = $filters['date_range'] ?? 'all';
        switch ($dateFilter) {
            case 'today':
                $query->whereDate('purchased_at', now()->toDateString());
                break;
            case 'yesterday':
                $query->whereDate('purchased_at', now()->subDay()->toDateString());
                break;
            case 'this_week':
                $query->whereBetween('purchased_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereBetween('purchased_at', [now()->startOfMonth(), now()->endOfMonth()]);
                break;
        }

        return $query->orderByDesc('purchased_at')->paginate($perPage)->withQueryString();
    }

    public function getSummary(Business $business): array
    {
        $todayPurchases = Purchase::where('business_id', $business->id)
            ->where('status', Purchase::STATUS_COMPLETED)
            ->whereDate('purchased_at', now()->toDateString());

        $monthPurchases = Purchase::where('business_id', $business->id)
            ->where('status', Purchase::STATUS_COMPLETED)
            ->whereBetween('purchased_at', [now()->startOfMonth(), now()->endOfMonth()]);

        return [
            'today_total' => (float)(clone $todayPurchases)->sum('total_amount'),
            'today_count' => (int)(clone $todayPurchases)->count(),
            'month_total' => (float)(clone $monthPurchases)->sum('total_amount'),
        ];
    }
}
