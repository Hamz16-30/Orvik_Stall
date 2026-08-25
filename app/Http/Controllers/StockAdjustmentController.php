<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\StockAdjustmentRequest;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function store(StockAdjustmentRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('adjustStock', $product);

        $movement = $this->inventoryService->adjustStock(
            product: $product,
            type: $request->type,
            quantity: (float)$request->quantity,
            reason: $request->reason,
            user: Auth::user()
        );

        return redirect()->route('app.inventory.show', $product->id)
            ->with('success', 'Stock adjusted successfully. Current quantity: ' . $movement->new_quantity . ' ' . $product->unit . '(s).');
    }
}
