@php
    $canUpdate = $user->hasPermissionTo('inventory.update');
    $canAdjust = ($user->isOwner() || $user->isManager()) && $canUpdate;
    $canDelete = $user->hasPermissionTo('inventory.delete');
    $canViewCost = $user->isOwner() || $user->isManager();
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="{{ $product->name }}" subtitle="Product details, stock metrics, and movements timeline" activeNav="inventory">
    <div x-data="{ adjustModalOpen: false, adjType: 'ADD', adjQty: 1, adjReason: '' }" class="max-w-5xl space-y-6">
        <!-- Top Navigation & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <a href="{{ route('app.inventory.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Inventory</span>
            </a>

            <div class="flex items-center gap-2">
                @if($canAdjust && !$product->trashed())
                    <x-ui.button variant="accent" size="sm" rounded="lg" icon="sliders" @click="adjustModalOpen = true">
                        Adjust Stock
                    </x-ui.button>
                @endif

                @if($canUpdate && !$product->trashed())
                    <x-ui.button variant="secondary" size="sm" rounded="lg" icon="edit-3" :href="route('app.inventory.edit', $product->id)">
                        Edit Details
                    </x-ui.button>
                @endif

                @if($canDelete)
                    @if($product->trashed())
                        <form method="POST" action="{{ route('app.inventory.restore', $product->id) }}">
                            @csrf
                            <x-ui.button type="submit" variant="secondary" size="sm" rounded="lg" icon="rotate-ccw">
                                Restore Product
                            </x-ui.button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('app.inventory.destroy', $product->id) }}" onsubmit="return confirm('Archive this product?');">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit" variant="destructive" size="sm" rounded="lg" icon="archive">
                                Archive
                            </x-ui.button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800 flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs font-medium text-rose-800 flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Product Overview Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Main Stats Card -->
            <div class="md:col-span-2 bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-xl font-bold text-foreground">{{ $product->name }}</h2>
                            @if($product->trashed())
                                <x-ui.badge variant="secondary" size="sm">Archived</x-ui.badge>
                            @endif
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Category: <strong>{{ $product->category ? $product->category->name : 'Uncategorized' }}</strong>
                        </p>
                    </div>

                    <div>
                        @if($product->stock_status === \App\Models\Product::STATUS_OUT_OF_STOCK)
                            <x-ui.badge variant="out-of-stock" size="md">Out of Stock</x-ui.badge>
                        @elseif($product->stock_status === \App\Models\Product::STATUS_LOW_STOCK)
                            <x-ui.badge variant="low-stock" size="md">Low Stock Alert</x-ui.badge>
                        @else
                            <x-ui.badge variant="in-stock" size="md">In Stock</x-ui.badge>
                        @endif
                    </div>
                </div>

                <!-- Price and Metric Highlights -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4 border-t border-border/60">
                    <div class="p-3 bg-secondary/30 rounded-xl">
                        <p class="text-[11px] text-muted-foreground font-medium">Selling Price</p>
                        <p class="text-base font-bold text-foreground mt-0.5">{{ $currency }}{{ number_format($product->selling_price, 2) }}</p>
                    </div>

                    @if($canViewCost)
                        <div class="p-3 bg-secondary/30 rounded-xl">
                            <p class="text-[11px] text-muted-foreground font-medium">Cost Price</p>
                            <p class="text-base font-bold text-foreground mt-0.5">
                                {{ $product->cost_price ? $currency . number_format($product->cost_price, 2) : 'N/A' }}
                            </p>
                        </div>
                    @endif

                    <div class="p-3 bg-secondary/30 rounded-xl">
                        <p class="text-[11px] text-muted-foreground font-medium">Current Stock</p>
                        <p class="text-base font-bold text-foreground font-mono mt-0.5">
                            {{ (float)$product->stock_quantity }} {{ $product->unit }}
                        </p>
                    </div>
                </div>

                @if($product->description)
                    <div class="text-xs text-muted-foreground bg-muted/20 p-3.5 rounded-xl border border-border/50">
                        <span class="font-semibold text-foreground block mb-1">Description:</span>
                        {{ $product->description }}
                    </div>
                @endif
            </div>

            <!-- Details Sidebar -->
            <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-4 text-xs">
                <h3 class="text-sm font-bold text-foreground pb-2 border-b border-border">Specifications</h3>

                <div class="flex justify-between py-1 border-b border-border/40">
                    <span class="text-muted-foreground">SKU</span>
                    <span class="font-mono font-semibold text-foreground">{{ $product->sku ?: '—' }}</span>
                </div>

                <div class="flex justify-between py-1 border-b border-border/40">
                    <span class="text-muted-foreground">Barcode</span>
                    <span class="font-mono font-semibold text-foreground">{{ $product->barcode ?: '—' }}</span>
                </div>

                <div class="flex justify-between py-1 border-b border-border/40">
                    <span class="text-muted-foreground">Low Stock Threshold</span>
                    <span class="font-semibold text-foreground">{{ (float)$product->low_stock_threshold }} {{ $product->unit }}</span>
                </div>

                <div class="flex justify-between py-1 border-b border-border/40">
                    <span class="text-muted-foreground">Track Inventory</span>
                    <span class="font-semibold text-foreground">{{ $product->track_inventory ? 'Yes' : 'No' }}</span>
                </div>

                <div class="flex justify-between py-1">
                    <span class="text-muted-foreground">Created Date</span>
                    <span class="font-semibold text-foreground">{{ $product->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Stock Movements History Timeline -->
        <x-ui.card title="Stock Movements History ({{ $product->stockMovements->count() }})" description="Immutable append-only ledger of stock adjustments, openings, and transactions">
            <x-ui.table :headers="['Date & Time', 'Operator', 'Movement Type', 'Qty Changed', 'Previous Stock', 'New Stock', 'Reason']">
                @forelse($product->stockMovements as $movement)
                    <tr class="hover:bg-muted/20 transition-colors text-xs">
                        <td class="px-5 py-3.5 font-mono text-muted-foreground whitespace-nowrap">
                            {{ $movement->created_at ? $movement->created_at->format('Y-m-d H:i') : 'N/A' }}
                        </td>

                        <td class="px-5 py-3.5 font-semibold text-foreground">
                            {{ $movement->user ? $movement->user->name : 'System' }}
                        </td>

                        <td class="px-5 py-3.5">
                            @if($movement->type === 'OPENING')
                                <x-ui.badge variant="secondary" size="sm" :dot="false">Opening Stock</x-ui.badge>
                            @elseif(str_contains($movement->type, 'ADD'))
                                <x-ui.badge variant="in-stock" size="sm" :dot="false">+ Stock Add</x-ui.badge>
                            @elseif(str_contains($movement->type, 'REMOVE'))
                                <x-ui.badge variant="destructive" size="sm" :dot="false">- Stock Remove</x-ui.badge>
                            @elseif(str_contains($movement->type, 'SET'))
                                <x-ui.badge variant="accent" size="sm" :dot="false">= Stock Set</x-ui.badge>
                            @else
                                <x-ui.badge variant="secondary" size="sm" :dot="false">{{ $movement->type }}</x-ui.badge>
                            @endif
                        </td>

                        <td class="px-5 py-3.5 font-mono font-bold text-foreground">
                            {{ (float)$movement->quantity }}
                        </td>

                        <td class="px-5 py-3.5 font-mono text-muted-foreground">
                            {{ (float)$movement->previous_quantity }}
                        </td>

                        <td class="px-5 py-3.5 font-mono font-bold text-foreground">
                            {{ (float)$movement->new_quantity }}
                        </td>

                        <td class="px-5 py-3.5 text-muted-foreground max-w-xs truncate">
                            {{ $movement->reason ?: '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-xs text-muted-foreground">
                            No stock movements recorded for this item.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </x-ui.card>

        <!-- Stock Adjustment Modal -->
        @if($canAdjust)
            <div x-show="adjustModalOpen" x-cloak class="relative z-50">
                <div class="fixed inset-0 bg-foreground/40 backdrop-blur-xs" @click="adjustModalOpen = false"></div>
                <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                    <div class="bg-white rounded-2xl border border-border shadow-2xl max-w-md w-full p-6 text-left" @click.stop>
                        <div class="flex items-center justify-between pb-3 border-b border-border/60">
                            <div>
                                <h3 class="text-base font-bold text-foreground">Adjust Stock: {{ $product->name }}</h3>
                                <p class="text-xs text-muted-foreground">Current Stock: {{ (float)$product->stock_quantity }} {{ $product->unit }}</p>
                            </div>
                            <button type="button" @click="adjustModalOpen = false" class="text-muted-foreground hover:text-foreground">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('app.inventory.stock.adjust', $product->id) }}" class="mt-4 space-y-4">
                            @csrf

                            <div>
                                <label class="block text-xs font-semibold text-foreground mb-1.5">Adjustment Action</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        type="button"
                                        @click="adjType = 'ADD'"
                                        :class="adjType === 'ADD' ? 'bg-emerald-600 text-white font-bold' : 'bg-secondary text-foreground hover:bg-muted'"
                                        class="py-2 text-xs rounded-lg transition-colors"
                                    >
                                        + Add Stock
                                    </button>
                                    <button
                                        type="button"
                                        @click="adjType = 'REMOVE'"
                                        :class="adjType === 'REMOVE' ? 'bg-rose-600 text-white font-bold' : 'bg-secondary text-foreground hover:bg-muted'"
                                        class="py-2 text-xs rounded-lg transition-colors"
                                    >
                                        - Remove Stock
                                    </button>
                                    <button
                                        type="button"
                                        @click="adjType = 'SET'"
                                        :class="adjType === 'SET' ? 'bg-accent text-white font-bold' : 'bg-secondary text-foreground hover:bg-muted'"
                                        class="py-2 text-xs rounded-lg transition-colors"
                                    >
                                        = Set Exact
                                    </button>
                                </div>
                                <input type="hidden" name="type" :value="adjType">
                            </div>

                            <x-ui.input
                                label="Quantity"
                                type="number"
                                step="0.001"
                                name="quantity"
                                id="adj_quantity"
                                placeholder="e.g. 10"
                                x-model="adjQty"
                                required
                            />

                            <x-ui.input
                                label="Reason / Notes"
                                name="reason"
                                id="adj_reason"
                                placeholder="e.g. Received shipment, Damaged item, Stock audit..."
                                x-model="adjReason"
                            />

                            <!-- Live Result Preview -->
                            <div class="p-3 bg-secondary/40 rounded-xl text-xs text-foreground flex items-center justify-between">
                                <span class="text-muted-foreground">Resulting Stock:</span>
                                <span class="font-mono font-bold text-foreground">
                                    <template x-if="adjType === 'ADD'">
                                        <span x-text="({{ (float)$product->stock_quantity }} + parseFloat(adjQty || 0)).toFixed(3) + ' {{ $product->unit }}'"></span>
                                    </template>
                                    <template x-if="adjType === 'REMOVE'">
                                        <span x-text="Math.max(0, ({{ (float)$product->stock_quantity }} - parseFloat(adjQty || 0))).toFixed(3) + ' {{ $product->unit }}'"></span>
                                    </template>
                                    <template x-if="adjType === 'SET'">
                                        <span x-text="parseFloat(adjQty || 0).toFixed(3) + ' {{ $product->unit }}'"></span>
                                    </template>
                                </span>
                            </div>

                            <div class="pt-3 border-t border-border flex items-center justify-end gap-3">
                                <x-ui.button type="button" variant="ghost" size="sm" @click="adjustModalOpen = false">
                                    Cancel
                                </x-ui.button>
                                <x-ui.button type="submit" variant="accent" size="sm" rounded="lg">
                                    Confirm Adjustment
                                </x-ui.button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
