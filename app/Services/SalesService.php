<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\SaaS\UsageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesService
{
    public function __construct(
        protected InvoiceService $invoiceService,
        protected InventoryService $inventoryService
    ) {}

    /**
     * Complete a POS sale atomically with concurrency protection and inventory deduction.
     */
    public function completeSale(array $data, Business $business, User $user): Sale
    {
        $usageService = app(UsageService::class);
        if (!$usageService->canCompleteSale($business)) {
            $usage = $usageService->getMonthlySalesUsage($business);
            throw ValidationException::withMessages([
                'plan_limit' => "This business has reached its monthly transaction limit of {$usage['limit']} sales. Upgrade your plan to continue.",
            ]);
        }

        $idempotencyToken = $data['idempotency_token'] ?? null;

        // 1. Idempotency Guard: prevent duplicate sales on double-click
        if ($idempotencyToken) {
            $existingSale = Sale::where('business_id', $business->id)
                ->where('idempotency_token', $idempotencyToken)
                ->with('items')
                ->first();

            if ($existingSale) {
                return $existingSale;
            }
        }

        $items = $data['items'] ?? [];
        if (empty($items)) {
            throw ValidationException::withMessages([
                'cart' => 'Cannot complete a sale with an empty cart.',
            ]);
        }

        return DB::transaction(function () use ($data, $business, $user, $items, $idempotencyToken) {
            $subtotal = '0.00';
            $validatedItems = [];

            // 2. Lock product rows, validate tenant, verify active status, and check stock
            foreach ($items as $item) {
                $productId = $item['product_id'];
                $quantity = (float)($item['quantity'] ?? 1);

                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Item quantity must be greater than zero.',
                    ]);
                }

                /** @var Product|null $product */
                $product = Product::lockForUpdate()->find($productId);

                if (!$product || (int)$product->business_id !== (int)$business->id) {
                    throw ValidationException::withMessages([
                        'cart' => 'One or more products in your cart do not belong to this stall.',
                    ]);
                }

                if ($product->status !== 'active' || $product->trashed()) {
                    throw ValidationException::withMessages([
                        'cart' => 'Product "' . $product->name . '" is archived or inactive and cannot be sold.',
                    ]);
                }

                if ($product->track_inventory) {
                    $currentStock = (float)$product->stock_quantity;
                    if ($currentStock < $quantity) {
                        throw ValidationException::withMessages([
                            'cart' => 'Insufficient stock for "' . $product->name . '". Available: ' . $currentStock . ' ' . $product->unit . '(s), requested: ' . $quantity . '.',
                        ]);
                    }
                }

                $unitPrice = (string)$product->selling_price;
                $costPrice = $product->cost_price !== null ? (string)$product->cost_price : null;
                $lineTotal = bcmul($unitPrice, (string)$quantity, 2);
                $subtotal = bcadd($subtotal, $lineTotal, 2);

                $validatedItems[] = [
                    'product' => $product,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => (float)$unitPrice,
                    'cost_price' => $costPrice !== null ? (float)$costPrice : null,
                    'line_total' => (float)$lineTotal,
                ];
            }

            // 3. Discount calculation & validation
            $discountAmount = isset($data['discount_amount']) ? (float)$data['discount_amount'] : 0.0;
            if ($discountAmount < 0) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Discount cannot be negative.',
                ]);
            }
            if ($discountAmount > (float)$subtotal) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Discount cannot exceed the order subtotal (' . $subtotal . ').',
                ]);
            }

            $taxAmount = isset($data['tax_amount']) ? (float)$data['tax_amount'] : 0.0;
            $totalAmount = (float)bcsub((string)$subtotal, (string)$discountAmount, 2);
            $totalAmount = (float)bcadd((string)$totalAmount, (string)$taxAmount, 2);

            // 4. Payment details and Cash Change calculation
            $paymentMethod = $data['payment_method'] ?? Sale::PAYMENT_CASH;
            $amountReceived = isset($data['amount_received']) && $data['amount_received'] !== '' ? (float)$data['amount_received'] : null;
            $changeAmount = null;

            if ($paymentMethod === Sale::PAYMENT_CASH) {
                if ($amountReceived !== null) {
                    if ($amountReceived < $totalAmount) {
                        throw ValidationException::withMessages([
                            'amount_received' => 'Amount received cannot be less than the total amount (' . number_format($totalAmount, 2) . ').',
                        ]);
                    }
                    $changeAmount = (float)bcsub((string)$amountReceived, (string)$totalAmount, 2);
                } else {
                    $amountReceived = $totalAmount;
                    $changeAmount = 0.0;
                }
            }

            // 5. Generate unique invoice number
            $invoiceNumber = $this->invoiceService->generateInvoiceNumber($business);

            // 6. Create Sale record
            $sale = Sale::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'customer_id' => $data['customer_id'] ?? null,
                'invoice_number' => $invoiceNumber,
                'subtotal' => (float)$subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'amount_received' => $amountReceived,
                'change_amount' => $changeAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => 'paid',
                'status' => Sale::STATUS_COMPLETED,
                'idempotency_token' => $idempotencyToken,
                'notes' => $data['notes'] ?? null,
                'completed_at' => now(),
            ]);

            // 7. Create Sale Items and Deduct Inventory
            foreach ($validatedItems as $vItem) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $vItem['product']->id,
                    'product_name' => $vItem['product_name'],
                    'sku' => $vItem['sku'],
                    'quantity' => $vItem['quantity'],
                    'unit_price' => $vItem['unit_price'],
                    'cost_price' => $vItem['cost_price'],
                    'discount_amount' => 0.00,
                    'line_total' => $vItem['line_total'],
                ]);

                // Deduct stock if tracked
                if ($vItem['product']->track_inventory) {
                    $prevStock = (float)$vItem['product']->stock_quantity;
                    $newStock = $prevStock - $vItem['quantity'];

                    $vItem['product']->update(['stock_quantity' => $newStock]);

                    StockMovement::create([
                        'business_id' => $business->id,
                        'product_id' => $vItem['product']->id,
                        'user_id' => $user->id,
                        'type' => StockMovement::TYPE_SALE,
                        'quantity' => -$vItem['quantity'],
                        'previous_quantity' => $prevStock,
                        'new_quantity' => $newStock,
                        'reason' => 'POS Sale #' . $invoiceNumber,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'created_at' => now(),
                    ]);
                }
            }

            // 8. Audit Log
            AuditLogger::log(
                action: 'sale.completed',
                entityType: Sale::class,
                entityId: $sale->id,
                newValues: [
                    'invoice_number' => $sale->invoice_number,
                    'total_amount' => $sale->total_amount,
                    'payment_method' => $sale->payment_method,
                    'items_count' => count($validatedItems),
                ],
                businessId: $business->id,
                userId: $user->id
            );

            return $sale->load('items');
        });
    }

    /**
     * Void a completed sale and reverse its stock movements.
     */
    public function voidSale(Sale $sale, string $reason, User $user): Sale
    {
        if ($sale->status !== Sale::STATUS_COMPLETED) {
            throw ValidationException::withMessages([
                'sale' => 'Only completed sales can be voided.',
            ]);
        }

        return DB::transaction(function () use ($sale, $reason, $user) {
            $sale->update([
                'status' => Sale::STATUS_VOIDED,
                'void_reason' => $reason,
                'voided_by' => $user->id,
                'voided_at' => now(),
            ]);

            // Reverse inventory for each tracked item
            foreach ($sale->items as $item) {
                if ($item->product_id) {
                    /** @var Product|null $product */
                    $product = Product::lockForUpdate()->find($item->product_id);
                    if ($product && $product->track_inventory) {
                        $prevStock = (float)$product->stock_quantity;
                        $newStock = $prevStock + (float)$item->quantity;

                        $product->update(['stock_quantity' => $newStock]);

                        StockMovement::create([
                            'business_id' => $sale->business_id,
                            'product_id' => $product->id,
                            'user_id' => $user->id,
                            'type' => StockMovement::TYPE_SALE_RETURN,
                            'quantity' => (float)$item->quantity,
                            'previous_quantity' => $prevStock,
                            'new_quantity' => $newStock,
                            'reason' => 'Voided Sale #' . $sale->invoice_number . ': ' . $reason,
                            'reference_type' => 'sale_void',
                            'reference_id' => $sale->id,
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            AuditLogger::log(
                action: 'sale.voided',
                entityType: Sale::class,
                entityId: $sale->id,
                oldValues: ['status' => 'completed'],
                newValues: ['status' => 'voided', 'reason' => $reason],
                businessId: $sale->business_id,
                userId: $user->id
            );

            return $sale;
        });
    }

    /**
     * Get summary statistics for today's sales.
     */
    public function getDailySummary(Business $business): array
    {
        $todaySales = Sale::where('business_id', $business->id)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereDate('completed_at', now()->toDateString());

        $totalRevenue = (float)(clone $todaySales)->sum('total_amount');
        $orderCount = (int)(clone $todaySales)->count();
        $averageOrder = $orderCount > 0 ? (float)bcdiv((string)$totalRevenue, (string)$orderCount, 2) : 0.0;

        return [
            'today_revenue' => $totalRevenue,
            'today_orders' => $orderCount,
            'average_order' => $averageOrder,
        ];
    }
}
