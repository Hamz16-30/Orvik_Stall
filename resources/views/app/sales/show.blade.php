@php
    $canVoid = ($user->isOwner() || $user->isManager()) && $sale->status === 'completed';
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Sale #{{ $sale->invoice_number }}" subtitle="Transaction breakdown and audit details" activeNav="sales">
    <div x-data="{ voidModalOpen: false }" class="max-w-4xl space-y-6">
        <!-- Back Navigation & Top Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <a href="{{ route('app.sales.history') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Sales History</span>
            </a>

            <div class="flex items-center gap-2">
                <x-ui.button variant="secondary" size="sm" rounded="lg" icon="printer" :href="route('app.sales.receipt', $sale->id)" target="_blank">
                    Print Receipt
                </x-ui.button>

                @if($canVoid)
                    <x-ui.button variant="destructive" size="sm" rounded="lg" icon="ban" @click="voidModalOpen = true">
                        Void Sale
                    </x-ui.button>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800 flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($sale->status === 'voided')
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 space-y-1">
                <div class="flex items-center gap-2 font-bold">
                    <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-600"></i>
                    <span>This sale was VOIDED on {{ $sale->voided_at ? $sale->voided_at->format('M d, Y H:i') : '' }} by {{ $sale->voidedByUser ? $sale->voidedByUser->name : 'Manager' }}</span>
                </div>
                <p class="text-rose-700 pl-6"><strong>Reason:</strong> {{ $sale->void_reason }}</p>
                <p class="text-[11px] text-rose-600 pl-6">Inventory quantities for all items in this order were safely returned to stock.</p>
            </div>
        @endif

        <!-- Sale Header Card -->
        <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-border/70">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold font-mono text-foreground">{{ $sale->invoice_number }}</h2>
                        @if($sale->status === 'completed')
                            <x-ui.badge variant="in-stock" size="sm">Completed</x-ui.badge>
                        @elseif($sale->status === 'voided')
                            <x-ui.badge variant="destructive" size="sm">Voided</x-ui.badge>
                        @endif
                    </div>
                    <p class="text-xs text-muted-foreground mt-1">
                        Completed on {{ $sale->completed_at ? $sale->completed_at->format('l, M d, Y \a\t H:i:s') : 'N/A' }}
                    </p>
                </div>

                <div class="text-left sm:text-right">
                    <span class="text-xs text-muted-foreground block">Total Amount</span>
                    <span class="text-2xl font-extrabold text-foreground font-mono">{{ $currency }}{{ number_format($sale->total_amount, 2) }}</span>
                </div>
            </div>

            <!-- Transaction Metadata Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div class="p-3 bg-secondary/30 rounded-xl">
                    <span class="text-muted-foreground block text-[11px]">Operating Cashier</span>
                    <span class="font-bold text-foreground mt-0.5 block">{{ $sale->user ? $sale->user->name : 'N/A' }}</span>
                </div>

                <div class="p-3 bg-secondary/30 rounded-xl">
                    <span class="text-muted-foreground block text-[11px]">Payment Method</span>
                    <span class="font-bold text-foreground capitalize mt-0.5 block">{{ str_replace('_', ' ', $sale->payment_method) }}</span>
                </div>

                <div class="p-3 bg-secondary/30 rounded-xl">
                    <span class="text-muted-foreground block text-[11px]">Amount Received</span>
                    <span class="font-bold text-foreground font-mono mt-0.5 block">
                        {{ $sale->amount_received ? $currency . number_format($sale->amount_received, 2) : '—' }}
                    </span>
                </div>

                <div class="p-3 bg-secondary/30 rounded-xl">
                    <span class="text-muted-foreground block text-[11px]">Change Returned</span>
                    <span class="font-bold text-emerald-600 font-mono mt-0.5 block">
                        {{ $sale->change_amount ? $currency . number_format($sale->change_amount, 2) : '0.00' }}
                    </span>
                </div>
            </div>

            <!-- Items Snapshot Table -->
            <div class="pt-2">
                <h3 class="text-sm font-bold text-foreground mb-3">Order Items ({{ $sale->items->count() }})</h3>
                <x-ui.table :headers="['Product Name', 'SKU', 'Unit Price', 'Quantity', 'Line Total']">
                    @foreach($sale->items as $item)
                        <tr class="hover:bg-muted/20 transition-colors text-xs">
                            <td class="px-5 py-3.5 font-bold text-foreground">
                                {{ $item->product_name }}
                            </td>

                            <td class="px-5 py-3.5 font-mono text-muted-foreground">
                                {{ $item->sku ?: '—' }}
                            </td>

                            <td class="px-5 py-3.5 font-mono text-foreground">
                                {{ $currency }}{{ number_format($item->unit_price, 2) }}
                            </td>

                            <td class="px-5 py-3.5 font-mono font-bold text-foreground">
                                {{ (float)$item->quantity }}
                            </td>

                            <td class="px-5 py-3.5 font-mono font-bold text-foreground text-right">
                                {{ $currency }}{{ number_format($item->line_total, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </div>

            <!-- Financial Summary Box -->
            <div class="flex justify-end pt-4 border-t border-border/70">
                <div class="w-full sm:w-72 space-y-2 text-xs">
                    <div class="flex justify-between text-muted-foreground">
                        <span>Subtotal:</span>
                        <span class="font-mono font-semibold text-foreground">{{ $currency }}{{ number_format($sale->subtotal, 2) }}</span>
                    </div>

                    @if($sale->discount_amount > 0)
                        <div class="flex justify-between text-emerald-700 font-semibold">
                            <span>Discount Applied:</span>
                            <span class="font-mono">-{{ $currency }}{{ number_format($sale->discount_amount, 2) }}</span>
                        </div>
                    @endif

                    @if($sale->tax_amount > 0)
                        <div class="flex justify-between text-muted-foreground">
                            <span>Tax:</span>
                            <span class="font-mono font-semibold text-foreground">{{ $currency }}{{ number_format($sale->tax_amount, 2) }}</span>
                        </div>
                    @endif

                    <div class="pt-2 border-t border-border flex justify-between text-base font-extrabold text-foreground">
                        <span>Total:</span>
                        <span class="font-mono">{{ $currency }}{{ number_format($sale->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Void Confirmation Modal -->
        @if($canVoid)
            <div x-show="voidModalOpen" x-cloak class="relative z-50">
                <div class="fixed inset-0 bg-foreground/40 backdrop-blur-xs" @click="voidModalOpen = false"></div>
                <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                    <div class="bg-white rounded-2xl border border-border shadow-2xl max-w-md w-full p-6 text-left space-y-4" @click.stop>
                        <div class="flex items-center justify-between pb-3 border-b border-border">
                            <h3 class="text-base font-bold text-foreground">Void Sale #{{ $sale->invoice_number }}</h3>
                            <button type="button" @click="voidModalOpen = false" class="text-muted-foreground hover:text-foreground">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <p class="text-xs text-muted-foreground">
                            Voiding this transaction will mark the order as VOIDED and return all item quantities back into active inventory. This action cannot be undone.
                        </p>

                        <form method="POST" action="{{ route('app.sales.void', $sale->id) }}" class="space-y-4">
                            @csrf

                            <x-ui.input
                                label="Reason for Voiding"
                                name="reason"
                                id="void_reason"
                                placeholder="e.g. Customer cancelled, Ring-up error, Defective item..."
                                required
                            />

                            <div class="pt-3 border-t border-border flex items-center justify-end gap-3">
                                <x-ui.button type="button" variant="ghost" size="sm" @click="voidModalOpen = false">
                                    Cancel
                                </x-ui.button>
                                <x-ui.button type="submit" variant="destructive" size="sm" rounded="lg">
                                    Confirm Void & Return Stock
                                </x-ui.button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
